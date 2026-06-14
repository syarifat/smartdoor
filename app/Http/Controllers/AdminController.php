<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kamar;
use App\Models\Penghuni;
use App\Models\AnggotaKeluarga;
use App\Models\PercobaanGagal;
use App\Models\LaporanKehilangan;
use App\Models\LogAkses;
use App\Models\Tagihan;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard()

    {
        // Total orang = kepala keluarga + anggota yang sudah terhubung ke penghuni
        $jumlahKepala  = Penghuni::count();
        $jumlahAnggota = AnggotaKeluarga::whereNotNull('penghuni_id')->count();

        // Pendaftar menunggu = user role penghuni yang belum punya record di tabel penghunis
        $pendaftarMenungguList = \App\Models\User::where('role', 'penghuni')
            ->doesntHave('penghuni')
            ->get();

        return view('admin.dashboard', [
            'totalKamar'            => Kamar::count(),
            'kamarTerisi'           => Kamar::where('status', 'terisi')->count(),
            'kamarKosong'           => Kamar::where('status', 'tersedia')->count(),
            'totalPenghuni'         => $jumlahKepala + $jumlahAnggota,
            'totalKK'               => $jumlahKepala,
            'pendaftarMenunggu'     => $pendaftarMenungguList->count(),
            // Detail untuk modal
            'penghuniList'          => Penghuni::with(['kamar', 'anggotaKeluargas'])->orderBy('nama')->get(),
            'kamarTerisiList'       => Kamar::where('status', 'terisi')->with(['penghuni.anggotaKeluargas'])->orderBy('nomor_kamar')->get(),
            'kamarKosongList'       => Kamar::where('status', 'tersedia')->orderBy('nomor_kamar')->get(),
            'kamarList'             => Kamar::with('penghuni')->orderBy('nomor_kamar')->get(),
            'pendaftarMenungguList' => $pendaftarMenungguList,
            'recentLogs'            => \App\Models\LogAkses::with(['penghuni', 'kamar'])->orderBy('waktu', 'desc')->take(5)->get(),
        ]);
    }

    public function log(Request $request)
    {
        $query = \App\Models\LogAkses::with(['penghuni', 'kamar'])->orderBy('waktu', 'desc');
        
        if ($request->has('metode') && in_array($request->metode, ['rfid', 'pin', 'web'])) {
            $query->where('metode_akses', $request->metode);
        }

        if ($request->has('status') && in_array($request->status, ['berhasil', 'ditolak'])) {
            $query->where('status', $request->status);
        }

        $logs = $query->paginate(20);
        return view('admin.log', compact('logs'));
    }



    public function percobaanGagal()
    {
        // Mark semua yang belum dilihat → sudah dilihat saat admin buka halaman
        PercobaanGagal::where('sudah_dilihat', false)->update(['sudah_dilihat' => true]);

        $data = PercobaanGagal::with('kamar')
            ->orderBy('waktu', 'desc')
            ->paginate(20);

        return view('admin.percobaan_gagal', compact('data'));
    }

    public function hapusPercobaan($id)
    {
        $record = PercobaanGagal::findOrFail($id);

        if ($record->foto_path) {
            Storage::disk('public')->delete($record->foto_path);
        }

        $record->delete();

        return redirect()->route('admin.percobaan.index')
            ->with('success', 'Data percobaan berhasil dihapus.');
    }

    public function hapusPendaftar($id)
    {
        $user = \App\Models\User::findOrFail($id);
        
        if ($user->role === 'penghuni' && !$user->penghuni) {
            $user->delete();
            return redirect()->back()->with('success', 'Pendaftar berhasil dihapus');
        }
        
        return redirect()->back()->with('error', 'Gagal menghapus pendaftar.');
    }

    // ── Fitur 2: Laporan Kehilangan Kartu ────────────────────────
    public function laporanKehilangan()
    {
        $laporan = LaporanKehilangan::with(['penghuni', 'kartu'])
            ->latest()
            ->paginate(20);

        return view('admin.laporan_kehilangan', compact('laporan'));
    }

    public function prosesLaporan($id)
    {
        LaporanKehilangan::findOrFail($id)->update(['status' => 'diproses']);
        return back()->with('success', 'Laporan ditandai sedang diproses.');
    }

    public function selesaikanLaporan(Request $request, $id)
    {
        $laporan = LaporanKehilangan::with('penghuni')->findOrFail($id);
        $laporan->update(['status' => 'selesai']);

        // Buat tagihan denda jika diisi dan belum pernah ditagihkan
        if ($request->filled('jumlah_denda') && !$laporan->denda_ditagihkan) {
            $jumlahDenda = (float) $request->jumlah_denda;

            if ($jumlahDenda > 0 && $laporan->penghuni) {
                $penghuni = $laporan->penghuni;

                Tagihan::create([
                    'penghuni_id'    => $penghuni->id,
                    'kamar_id'       => $penghuni->kamar_id,
                    'bulan'          => now()->translatedFormat('F Y'),
                    'jumlah_tagihan' => $jumlahDenda,
                    'tanggal_tagihan'=> now()->toDateString(),
                    'keterangan'     => 'Denda kehilangan kartu akses RFID',
                    'status'         => 'belum_bayar',
                ]);

                $laporan->update([
                    'jumlah_denda'     => $jumlahDenda,
                    'denda_ditagihkan' => true,
                ]);
            }
        }

        return back()->with('success', 'Laporan ditandai selesai' . ($request->filled('jumlah_denda') ? ' dan tagihan denda telah dibuat.' : '.'));
    }

    public function batalDenda($id)
    {
        $laporan = LaporanKehilangan::with('penghuni')->findOrFail($id);

        if ($laporan->denda_ditagihkan && $laporan->penghuni) {
            // Hapus tagihan denda
            $tagihan = Tagihan::where('penghuni_id', $laporan->penghuni->id)
                ->where('keterangan', 'LIKE', 'Denda kehilangan kartu akses RFID%')
                ->latest()
                ->first();
                
            if ($tagihan) {
                $tagihan->delete();
            }

            // Reset status denda di laporan
            $laporan->update([
                'jumlah_denda' => null,
                'denda_ditagihkan' => false,
            ]);

            return back()->with('success', 'Denda berhasil dibatalkan dan tagihan denda terkait telah dihapus.');
        }

        return back()->with('error', 'Laporan ini tidak memiliki denda yang ditagihkan atau tagihan sudah tidak tersedia.');
    }

    // ── Fitur 4: Real-time JSON endpoint untuk dashboard ─────────
    public function aktivitasTerbaru()
    {
        $logs = LogAkses::with(['penghuni', 'kamar'])
            ->orderBy('waktu', 'desc')
            ->take(5)
            ->get()
            ->map(fn($l) => [
                'penghuni'  => $l->penghuni?->nama ?? 'Tidak Dikenal (UID: ' . $l->uid . ')',
                'kamar'     => $l->kamar ? 'Kamar ' . $l->kamar->nomor_kamar : '-',
                'aksi'      => $l->aksi,
                'status'    => $l->status,
                'keterangan'=> $l->keterangan ?? '-',
                'waktu'     => $l->waktu->diffForHumans(),
            ]);

        $belumDilihat = PercobaanGagal::where('sudah_dilihat', false)->count();

        return response()->json([
            'logs'          => $logs,
            'belum_dilihat' => $belumDilihat,
        ]);
    }

    // ── Fitur 5: Update Nomor HP Admin ─────────
    public function updateNomorHp(Request $request)
    {
        $request->validate([
            'telepon' => 'required|string|max:20',
        ]);

        $user = auth()->user();
        if ($user && $user->role === 'admin') {
            $user->telepon = $request->telepon;
            $user->save();
            return back()->with('success', 'Nomor WhatsApp berhasil diperbarui.');
        }

        return back()->with('error', 'Gagal memperbarui nomor WhatsApp.');
    }

    public function aksesDarurat()
    {
        return view('admin.akses_darurat');
    }

    public function bukaSemuaPintu()
    {
        $kamars = \App\Models\Kamar::all();
        $now = now();

        foreach ($kamars as $kamar) {
            $kamar->update([
                'perintah' => 'buka_darurat',
                'terakhir_diakses' => $now
            ]);

            \App\Models\LogAkses::create([
                'uid'          => 'MASTER_WEB',
                'penghuni_id'  => null,
                'kamar_id'     => $kamar->id,
                'status'       => 'berhasil',
                'aksi'         => 'masuk',
                'keterangan'   => 'Akses darurat pemilik kos - semua pintu dibuka',
                'metode_akses' => 'web',
                'waktu'        => $now
            ]);
        }

        return back()->with('success', 'Perintah berhasil dikirim: Semua pintu kamar terbuka!');
    }
}