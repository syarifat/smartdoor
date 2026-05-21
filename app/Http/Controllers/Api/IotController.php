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

        $kartu = Kartu::where('uid', $request->uid)->with('penghuni')->first();

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
                $keterangan = 'Akses diizinkan';
                $penghuniId = $kartu->penghuni->id;

                // Trigger buka pintu
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
                'status' => 'gagal',
                'pesan' => 'Terlalu banyak percobaan. Coba lagi setelah 5 menit.',
                'buka_pintu' => false
            ], 429);
        }

        $penghuni = Penghuni::where('kamar_id', $kamarId)->first();

        if (!$penghuni || !$penghuni->pin_aktif || !$penghuni->pin) {
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'PIN tidak aktif atau tidak ditemukan.',
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
                'buka_pintu' => true
            ], 200);

        } else {
            // Gagal
            $attempts++;
            Cache::put($cacheKey, $attempts, now()->addMinutes(5));

            LogAkses::create([
                'uid'         => 'KEYPAD',
                'penghuni_id' => $penghuni->id,
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
            }

            return response()->json([
                'status' => 'gagal',
                'pesan' => 'PIN salah.',
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
