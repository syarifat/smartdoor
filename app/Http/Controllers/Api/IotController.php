<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kamar;
use App\Models\LogAkses;
use App\Models\Kartu;
use App\Models\Penghuni;
use App\Models\PercobaanGagal;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class IotController extends Controller
{
    /**
     * Endpoint untuk menerima data dari sensor pintu (Terbuka / Tertutup)
     */
    public function updateDoorStatus(Request $request)
    {
        $request->validate([
            'nomor_kamar'  => 'required',
            'status_pintu' => 'required|in:terbuka,tertutup'
        ]);

        $kamar = Kamar::where('nomor_kamar', $request->nomor_kamar)->first();
        
        if (!$kamar) {
            return response()->json(['success' => false, 'message' => 'Kamar tidak ditemukan'], 404);
        }

        $kamar->update([
            'status_pintu' => $request->status_pintu,
            'terakhir_diakses' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status pintu kamar ' . $kamar->nomor_kamar . ' berhasil diupdate menjadi ' . $kamar->status_pintu
        ]);
    }

    /**
     * Endpoint untuk menerima scan RFID / Akses Pintu
     */
    public function recordAccess(Request $request)
    {
        $request->validate([
            'uid'         => 'required',
            'nomor_kamar' => 'required',
            'aksi'        => 'required|in:masuk,keluar'
        ]);

        $kamar = Kamar::where('nomor_kamar', $request->nomor_kamar)->first();
        if (!$kamar) {
            return response()->json(['success' => false, 'message' => 'Kamar tidak valid'], 404);
        }

        // Cek apakah ini kartu owner (bisa membuka semua kamar) dari database settings
        $ownerUidSetting = \App\Models\Setting::where('key', 'owner_card_uid')->first();
        $ownerUid = $ownerUidSetting ? $ownerUidSetting->value : null;
        if ($ownerUid && strtoupper($request->uid) === strtoupper($ownerUid)) {
            LogAkses::create([
                'uid'         => $request->uid,
                'penghuni_id' => null,
                'kamar_id'    => $kamar->id,
                'status'      => 'berhasil',
                'aksi'        => $request->aksi,
                'keterangan'  => 'Akses Owner (Membuka Semua Pintu)',
                'metode_akses'=> 'rfid'
            ]);

            $kamar->update([
                'status_pintu' => 'terbuka',
                'terakhir_diakses' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Akses Owner Diizinkan',
                'ambil_foto' => false
            ], 200);
        }

        $kartu = Kartu::where('uid', $request->uid)->with('penghuni')->first();
        $penghuniCadangan = Penghuni::where('uid_kartu_cadangan', $request->uid)->first();

        // Tentukan status akses
        $statusAkses = 'ditolak';
        $keterangan = 'Kartu tidak terdaftar';
        $penghuniId = null;

        if ($kartu) {
            if ($kartu->status !== 'aktif') {
                $keterangan = 'Kartu tidak aktif / diblokir';
            } elseif (!$kartu->penghuni) {
                $keterangan = 'Kartu belum dihubungkan dengan penghuni';
            } elseif ($kartu->penghuni->kamar_id !== $kamar->id) {
                $keterangan = 'Bukan kamar penghuni tersebut';
                $penghuniId = $kartu->penghuni->id;
            } else {
                // Semua valid
                $statusAkses = 'berhasil';
                $keterangan = 'Akses via Kartu RFID';
                $penghuniId = $kartu->penghuni->id;

                // Trigger buka pintu
                $kamar->update([
                    'status_pintu' => 'terbuka',
                    'terakhir_diakses' => now()
                ]);
            }
        }

        // Cek kartu cadangan jika verifikasi kartu utama belum berhasil
        if ($statusAkses !== 'berhasil' && $penghuniCadangan) {
            if ($penghuniCadangan->kamar_id !== $kamar->id) {
                $keterangan = 'Bukan kamar penghuni tersebut (Cadangan)';
                $penghuniId = $penghuniCadangan->id;
            } else {
                $statusAkses = 'berhasil';
                $keterangan = 'Akses via Kartu e-KTP/Cadangan';
                $penghuniId = $penghuniCadangan->id;

                $kamar->update([
                    'status_pintu' => 'terbuka',
                    'terakhir_diakses' => now()
                ]);
            }
        }

        // Catat ke Log Akses
        LogAkses::create([
            'uid'         => $request->uid,
            'penghuni_id' => $penghuniId,
            'kamar_id'    => $kamar->id,
            'status'      => $statusAkses,
            'aksi'        => $request->aksi,
            'keterangan'  => $keterangan,
            'metode_akses'=> 'rfid'
        ]);

        $ambilFoto = false;
        if ($statusAkses !== 'berhasil') {
            $kamarId = $kamar->id;
            $cacheKey = "rfid_attempts_{$kamarId}";
            $attempts = Cache::get($cacheKey, 0);
            $attempts++;
            Cache::put($cacheKey, $attempts, now()->addMinutes(5));

            if ($attempts >= 3) {
                $ambilFoto = true;
                PercobaanGagal::create([
                    'kamar_id'         => $kamarId,
                    'rfid_uid'         => $request->uid,
                    'jumlah_percobaan' => $attempts,
                    'sudah_dilihat'    => false,
                    'waktu'            => now()
                ]);
                // Reset counter ke 0 agar percobaan 4,5 tidak memicu foto
                // Foto baru hanya dipicu setiap kelipatan 3 kegagalan
                Cache::put($cacheKey, 0, now()->addMinutes(5));
            }
        } else {
            // Reset attempts on success
            Cache::forget("rfid_attempts_{$kamar->id}");
        }

        return response()->json([
            'success' => $statusAkses === 'berhasil',
            'message' => $keterangan,
            'ambil_foto' => $ambilFoto
        ], $statusAkses === 'berhasil' ? 200 : 403);
    }

    /**
     * Endpoint untuk menerima akses via PIN keypad (ESP32)
     */
    public function aksesPin(Request $request)
    {
        $request->validate([
            'kamar_id' => 'required|exists:kamars,id',
            'pin'      => 'required|digits:6'
        ]);

        $kamarId = $request->kamar_id;
        $ipAddress = $request->ip();
        $cacheKey = "pin_attempts_{$kamarId}_{$ipAddress}";
        
        $attempts = Cache::get($cacheKey, 0);

        if ($attempts >= 3) {
            return response()->json([
                'status' => 'ditolak',
                'pesan' => 'Terlalu banyak percobaan. Coba lagi setelah 5 menit.',
                'message' => 'Terlalu banyak percobaan. Coba lagi setelah 5 menit.',
                'buka_pintu' => false
            ], 429);
        }

        // Cek PIN Master (Pemilik Kos)
        $masterPin = \App\Models\Setting::where('key', 'master_pin')->first();
        if ($masterPin && $masterPin->value && Hash::check($request->pin, $masterPin->value)) {
            // Master PIN cocok, cek apakah kamar diizinkan
            $masterPinRooms = \App\Models\Setting::where('key', 'master_pin_rooms')->first();
            $allowedRooms = $masterPinRooms && $masterPinRooms->value ? json_decode($masterPinRooms->value, true) : [];
            
            if (in_array($kamarId, $allowedRooms)) {
                Cache::forget($cacheKey);

                Kamar::where('id', $kamarId)->update([
                    'status_pintu' => 'terbuka',
                    'terakhir_diakses' => now()
                ]);

                LogAkses::create([
                    'uid'         => 'MASTER_KEYPAD',
                    'penghuni_id' => null,
                    'kamar_id'    => $kamarId,
                    'status'      => 'berhasil',
                    'aksi'        => 'masuk',
                    'keterangan'  => 'Akses menggunakan PIN khusus pemilik kos',
                    'metode_akses'=> 'pin'
                ]);

                return response()->json([
                    'status' => 'berhasil',
                    'tipe_akses' => 'master_pin',
                    'pesan' => 'Akses pemilik kos berhasil',
                    'message' => 'Akses pemilik kos berhasil',
                    'buka_pintu' => true
                ], 200);
            } else {
                return response()->json([
                    'status' => 'ditolak',
                    'pesan' => 'Master PIN tidak diizinkan untuk kamar ini.',
                    'message' => 'Master PIN tidak diizinkan untuk kamar ini.',
                    'buka_pintu' => false
                ], 403);
            }
        }

        $penghuni = Penghuni::where('kamar_id', $kamarId)->first();

        if (!$penghuni || !$penghuni->pin_aktif || !$penghuni->pin) {
            return response()->json([
                'status' => 'ditolak',
                'pesan' => 'PIN tidak aktif atau tidak ditemukan.',
                'message' => 'PIN tidak aktif atau tidak ditemukan.',
                'buka_pintu' => false
            ], 403);
        }

        if (Hash::check($request->pin, $penghuni->pin)) {
            // Berhasil
            Cache::forget($cacheKey); // Reset attempt
            
            Kamar::where('id', $kamarId)->update([
                'status_pintu' => 'terbuka',
                'terakhir_diakses' => now()
            ]);

            LogAkses::create([
                'uid'         => 'KEYPAD',
                'penghuni_id' => $penghuni->id,
                'kamar_id'    => $kamarId,
                'status'      => 'berhasil',
                'aksi'        => 'masuk',
                'keterangan'  => 'Akses via PIN Keypad',
                'metode_akses'=> 'pin'
            ]);

            return response()->json([
                'status' => 'berhasil',
                'pesan' => 'Akses PIN diterima',
                'message' => 'Akses PIN diterima',
                'buka_pintu' => true
            ], 200);

        } else {
            // Gagal
            $attempts++;
            Cache::put($cacheKey, $attempts, now()->addMinutes(5));

            LogAkses::create([
                'uid'         => 'KEYPAD',
                'penghuni_id' => $penghuni ? $penghuni->id : null,
                'kamar_id'    => $kamarId,
                'status'      => 'ditolak',
                'aksi'        => 'masuk',
                'keterangan'  => 'PIN Keypad salah',
                'metode_akses'=> 'pin'
            ]);

            // Jika percobaan gagal 3x, minta ESP32 ambil foto
            $ambilFoto = false;
            if ($attempts >= 3) {
                $ambilFoto = true;
                // Catat ke percobaan_gagals
                PercobaanGagal::create([
                    'kamar_id'         => $kamarId,
                    'rfid_uid'         => 'KEYPAD',
                    'jumlah_percobaan' => $attempts,
                    'sudah_dilihat'    => false,
                    'waktu'            => now()
                ]);
                // Reset counter ke 0 agar percobaan 4,5 tidak memicu foto
                // Foto baru hanya dipicu setiap kelipatan 3 kegagalan
                Cache::put($cacheKey, 0, now()->addMinutes(5));
            }

            return response()->json([
                'status' => 'ditolak',
                'pesan' => 'PIN tidak valid',
                'message' => 'PIN tidak valid',
                'buka_pintu' => false,
                'ambil_foto' => $ambilFoto
            ], 403);
        }
    }



    /**
     * Endpoint untuk menerima laporan percobaan akses gagal + foto dari ESP32-CAM
     * POST /api/iot/percobaan-gagal
     */
    public function percobaanGagal(Request $request)
    {
        $request->validate([
            'kamar_id'          => 'required|exists:kamars,id',
            'foto'              => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('snapshots', 'public');
        }

        // Cari percobaan gagal terakhir untuk kamar ini dalam 2 menit terakhir (dibuat oleh ESP32 Utama)
        $latest = PercobaanGagal::where('kamar_id', $request->kamar_id)
            ->where('waktu', '>=', now()->subMinutes(2))
            ->latest('waktu')
            ->first();

        if ($latest) {
            $latest->update(['foto_path' => $fotoPath]);
        } else {
            // Fallback jika tidak ada record sebelumnya
            PercobaanGagal::create([
                'kamar_id'         => $request->kamar_id,
                'rfid_uid'         => $request->rfid_uid ?? 'CAMERA_ONLY',
                'jumlah_percobaan' => $request->jumlah_percobaan ?? 3,
                'foto_path'        => $fotoPath,
                'sudah_dilihat'    => false,
                'waktu'            => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Laporan percobaan gagal berhasil dicatat'
        ]);
    }

    /**
     * GET /api/iot/status-pintu/{kamar_id}
     * ESP32 polling setiap 2 detik — tidak perlu auth
     */
    public function statusPintu($kamar_id)
    {
        $kamar = Kamar::find($kamar_id);

        if (!$kamar) {
            return response()->json(['success' => false, 'message' => 'Kamar tidak ditemukan'], 404);
        }

        // AUTO-LOCK FALLBACK: Jika terbuka lebih dari 5 detik, ubah otomatis ke tertutup
        if ($kamar->status_pintu === 'terbuka' && $kamar->terakhir_diakses) {
            if ($kamar->terakhir_diakses->addSeconds(5)->isPast()) {
                $kamar->update([
                    'status_pintu' => 'tertutup'
                ]);
            }
        }

        return response()->json([
            'kamar_id'     => $kamar->id,
            'nomor_kamar'  => $kamar->nomor_kamar,
            'perintah'     => $kamar->perintah,
            'status_pintu' => $kamar->status_pintu,
            'updated_at'   => $kamar->updated_at?->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * POST /api/iot/konfirmasi-perintah/{kamar_id}
     * ESP32 konfirmasi bahwa perintah sudah dieksekusi
     */
    public function konfirmasiPerintah(Request $request, $kamar_id)
    {
        $request->validate([
            'status_pintu' => 'required|in:terbuka,tertutup',
        ]);

        $kamar = Kamar::find($kamar_id);

        if (!$kamar) {
            return response()->json(['success' => false, 'message' => 'Kamar tidak ditemukan'], 404);
        }

        $kamar->update([
            'status_pintu'    => $request->status_pintu,
            'perintah'        => null,   // reset perintah setelah dieksekusi
            'terakhir_diakses'=> now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Konfirmasi diterima. Status pintu: ' . $request->status_pintu,
        ]);
    }
}
