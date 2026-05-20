<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaporanGangguan;
use App\Models\Penghuni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LaporanGangguanController extends Controller
{
    // Fonnte API base URL
    private string $fonnteUrl = 'https://api.fonnte.com/send';

    /**
     * Kirim WhatsApp via Fonnte
     */
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
            // Log tapi jangan crash aplikasi
            \Log::warning('Gagal kirim WA: ' . $e->getMessage());
        }
    }

    /**
     * Dashboard daftar laporan gangguan
     */
    public function index(Request $request)
    {
        $query = LaporanGangguan::latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('no_laporan', 'like', '%' . $request->search . '%')
                  ->orWhere('nama_penghuni', 'like', '%' . $request->search . '%')
                  ->orWhere('no_kamar', 'like', '%' . $request->search . '%');
            });
        }

        $laporan = $query->paginate(15)->withQueryString();

        $stats = [
            'baru'     => LaporanGangguan::where('status', 'baru')->count(),
            'diproses' => LaporanGangguan::where('status', 'diproses')->count(),
            'selesai'  => LaporanGangguan::where('status', 'selesai')->count(),

        ];

        return view('admin.laporan_gangguan.index', compact('laporan', 'stats'));
    }

    /**
     * Detail laporan
     */
    public function show(LaporanGangguan $laporanGangguan)
    {
        return view('admin.laporan_gangguan.show', compact('laporanGangguan'));
    }

    /**
     * Ubah status ke "Diproses" → kirim WA ke penghuni
     */
    public function proses(Request $request, LaporanGangguan $laporanGangguan)
    {
        if ($laporanGangguan->status !== 'baru') {
            return back()->with('error', 'Laporan ini sudah diproses atau selesai.');
        }

        $request->validate([
            'catatan_admin' => 'nullable|string|max:500',
        ]);

        $laporanGangguan->update([
            'status'        => 'diproses',
            'catatan_admin' => $request->catatan_admin,
        ]);

        // Kirim notifikasi WA ke penghuni
        $penghuni = Penghuni::where('nama', $laporanGangguan->nama_penghuni)
            ->whereHas('kamar', fn($q) => $q->where('nomor_kamar', $laporanGangguan->no_kamar))
            ->first();

        if ($penghuni && $penghuni->telepon) {
            $pesan = "📋 *Update Laporan Gangguan*\n\n"
                   . "Halo *{$laporanGangguan->nama_penghuni}*,\n"
                   . "Laporan Anda dengan nomor *{$laporanGangguan->no_laporan}* sedang ditangani oleh tim kami.\n\n"
                   . "🔧 Kategori: {$laporanGangguan->kategori}\n"
                   . "📝 Status: *Sedang Diproses*\n\n"
                   . "Terima kasih atas kesabaran Anda. – Kos Bu Rini";
            $this->kirimWA($penghuni->telepon, $pesan);
        }

        return back()->with('success', "Laporan {$laporanGangguan->no_laporan} ditandai sedang diproses.");
    }

    /**
     * Ubah status ke "Selesai" → kirim WA ke penghuni
     */
    public function selesai(Request $request, LaporanGangguan $laporanGangguan)
    {
        if ($laporanGangguan->status === 'selesai') {
            return back()->with('error', 'Laporan ini sudah selesai.');
        }

        $request->validate([
            'catatan_admin' => 'nullable|string|max:500',
        ]);

        $laporanGangguan->update([
            'status'        => 'selesai',
            'catatan_admin' => $request->catatan_admin ?? $laporanGangguan->catatan_admin,
        ]);

        // Kirim notifikasi WA ke penghuni
        $penghuni = Penghuni::where('nama', $laporanGangguan->nama_penghuni)
            ->whereHas('kamar', fn($q) => $q->where('nomor_kamar', $laporanGangguan->no_kamar))
            ->first();

        if ($penghuni && $penghuni->telepon) {
            $pesan = "✅ *Laporan Gangguan Selesai*\n\n"
                   . "Halo *{$laporanGangguan->nama_penghuni}*,\n"
                   . "Laporan Anda dengan nomor *{$laporanGangguan->no_laporan}* telah selesai ditangani.\n\n"
                   . "🔧 Kategori: {$laporanGangguan->kategori}\n"
                   . "📝 Status: *Selesai*\n\n"
                   . "Terima kasih telah melapor. Semoga kamar Anda kembali nyaman! 🙏 – Kos Bu Rini";
            $this->kirimWA($penghuni->telepon, $pesan);
        }

        return back()->with('success', "Laporan {$laporanGangguan->no_laporan} telah diselesaikan.");
    }

    /**
     * Hapus laporan
     */
    public function destroy(LaporanGangguan $laporanGangguan)
    {
        if ($laporanGangguan->foto_bukti) {
            \Storage::disk('public')->delete($laporanGangguan->foto_bukti);
        }

        $noLaporan = $laporanGangguan->no_laporan;
        $laporanGangguan->delete();

        return redirect()->route('admin.laporan-gangguan.index')
            ->with('success', "Laporan {$noLaporan} berhasil dihapus.");
    }
}
