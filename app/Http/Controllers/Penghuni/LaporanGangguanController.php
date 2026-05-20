<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Models\LaporanGangguan;
use App\Models\Penghuni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class LaporanGangguanController extends Controller
{
    private string $fonnteUrl = 'https://api.fonnte.com/send';

    private function kirimWA(string $nomorTujuan, string $pesan): void
    {
        $token = env('FONNTE_TOKEN', '');
        if (empty($token) || empty($nomorTujuan)) return;

        try {
            Http::withHeaders(['Authorization' => $token])
                ->post($this->fonnteUrl, [
                    'target'  => $nomorTujuan,
                    'message' => $pesan,
                ]);
        } catch (\Throwable $e) {
            \Log::warning('Gagal kirim WA: ' . $e->getMessage());
        }
    }

    /**
     * Form laporan & riwayat penghuni
     */
    public function index()
    {
        $user     = auth()->user();
        $penghuni = $user->penghuni;

        $adminNomor = \App\Models\User::where('role', 'admin')->first()->telepon ?? env('ADMIN_WHATSAPP');

        if (!$penghuni || !$penghuni->kamar) {
            return view('penghuni.laporan_gangguan.index', [
                'riwayat'  => collect(),
                'penghuni' => null,
                'noKamar'  => '-',
                'adminNomor' => $adminNomor,
            ]);
        }

        $riwayat = LaporanGangguan::where('no_kamar', $penghuni->kamar->nomor_kamar)
            ->where('nama_penghuni', $penghuni->nama)
            ->latest()
            ->get();

        return view('penghuni.laporan_gangguan.index', [
            'riwayat'  => $riwayat,
            'penghuni' => $penghuni,
            'noKamar'  => $penghuni->kamar->nomor_kamar,
            'adminNomor' => $adminNomor,
        ]);
    }

    /**
     * Simpan laporan baru & kirim notifikasi WA
     */
    public function store(Request $request)
    {
        $request->validate([
            'kategori'   => 'required|in:Listrik,Air,Furnitur,Pintu & Kunci,Internet,Lainnya',
            'deskripsi'  => 'required|string|max:1000',

            'foto_bukti' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        $penghuni = auth()->user()->penghuni;

        if (!$penghuni || !$penghuni->kamar) {
            return back()->with('error', 'Anda belum memiliki kamar aktif. Hubungi admin.');
        }

        $noKamar   = $penghuni->kamar->nomor_kamar;
        $noLaporan = LaporanGangguan::generateNoLaporan($noKamar);

        $fotoBukti = null;
        if ($request->hasFile('foto_bukti')) {
            $fotoBukti = $request->file('foto_bukti')->store('laporan_gangguan', 'public');
        }

        $laporan = LaporanGangguan::create([
            'no_laporan'    => $noLaporan,
            'nama_penghuni' => $penghuni->nama,
            'no_kamar'      => $noKamar,
            'kategori'      => $request->kategori,
            'deskripsi'     => $request->deskripsi,
            'urgensi'       => 'normal',
            'foto_bukti'    => $fotoBukti,
            'status'        => 'baru',
        ]);

        // Trigger 1: Notifikasi WA ke penghuni
        if ($penghuni->telepon) {
            $pesanPenghuni = "📋 *Laporan Gangguan Diterima*\n\n"
                . "Halo *{$penghuni->nama}*,\n"
                . "Laporan Anda telah berhasil dikirim.\n\n"
                . "📌 No. Laporan: *{$noLaporan}*\n"
                . "🔧 Kategori: {$request->kategori}\n"
                . "📝 Status: *Baru*\n\n"
                . "mohon maaf atas ketidaknyamananya,Kami akan segera menindak lanjuti. Terima kasih! – Kos Bu Rini";
            $this->kirimWA($penghuni->telepon, $pesanPenghuni);
        }

        // Trigger 1: Notifikasi WA ke admin
        $adminNomor = \App\Models\User::where('role', 'admin')->first()->telepon ?? env('ADMIN_WHATSAPP');
        if ($adminNomor) {
            $pesanAdmin = "🚨 *Laporan Gangguan Baru Masuk*\n\n"
                . "📌 No. Laporan: *{$noLaporan}*\n"
                . "👤 Penghuni: {$penghuni->nama}\n"
                . "🏠 Kamar: {$noKamar}\n"
                . "🔧 Kategori: {$request->kategori}\n\n"
                . "silahkan cek website kos di laporan gangguan";
            $this->kirimWA($adminNomor, $pesanAdmin);
        }

        return back()->with('success', "Laporan berhasil dikirim! Nomor laporan Anda: *{$noLaporan}*");
    }

    /**
     * Tracking status via nomor laporan (publik untuk penghuni)
     */
    public function tracking(Request $request)
    {
        $laporan = null;
        if ($request->filled('no_laporan')) {
            $laporan = LaporanGangguan::where('no_laporan', strtoupper($request->no_laporan))->first();
        }
        return view('penghuni.laporan_gangguan.tracking', compact('laporan'));
    }
}
