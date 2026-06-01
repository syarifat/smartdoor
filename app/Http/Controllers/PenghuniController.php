<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaporanKehilangan;
use App\Models\Kartu;

class PenghuniController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $nomorKamar = '-';
        $kamar = null;
        $aksesHariIni = 0;
        $riwayatAkses = collect();

        if ($user && $user->penghuni && $user->penghuni->kamar) {
            $kamar = $user->penghuni->kamar;
            $nomorKamar = $kamar->nomor_kamar;
            
            $aksesHariIni = \App\Models\LogAkses::where('kamar_id', $kamar->id)
                                ->whereDate('waktu', today())
                                ->count();
                                
            $riwayatAkses = \App\Models\LogAkses::where('kamar_id', $kamar->id)
                                ->orderBy('waktu', 'desc')
                                ->take(5)
                                ->get();
        }

        return view('penghuni.dashboard', compact('nomorKamar', 'kamar', 'aksesHariIni', 'riwayatAkses'));
    }

    public function akses()
    {
        $user = auth()->user();
        $kamar = null;
        $logs = collect();

        if ($user && $user->penghuni && $user->penghuni->kamar) {
            $kamar = $user->penghuni->kamar;
            $logs = \App\Models\LogAkses::with('penghuni')
                ->where('kamar_id', $kamar->id)
                ->orderBy('waktu', 'desc')
                ->paginate(10);
        }

        return view('penghuni.akses', compact('kamar', 'logs'));
    }



    public function laporKehilangan()
    {
        $user    = auth()->user();
        $penghuni = $user->penghuni;

        // Kartu aktif/nonaktif milik penghuni ini
        $kartus = $penghuni
            ? Kartu::where('penghuni_id', $penghuni->id)->get()
            : collect();

        // Riwayat laporan milik penghuni
        $riwayat = $penghuni
            ? LaporanKehilangan::with('kartu')
                ->where('penghuni_id', $penghuni->id)
                ->latest()
                ->get()
            : collect();

        return view('penghuni.laporan_kehilangan', compact('kartus', 'riwayat'));
    }

    public function storeLaporan(Request $request)
    {
        $request->validate([
            'kartu_id'    => 'nullable|exists:kartu,id',
            'keterangan'  => 'required|string|max:500',
        ]);

        $penghuni = auth()->user()->penghuni;

        if (!$penghuni) {
            return back()->with('error', 'Anda belum terdaftar sebagai penghuni.');
        }

        // Nonaktifkan kartu yang dilaporkan
        if ($request->kartu_id) {
            Kartu::where('id', $request->kartu_id)
                ->where('penghuni_id', $penghuni->id) // pastikan punya penghuni ini
                ->update(['status' => 'nonaktif']);
        }

        LaporanKehilangan::create([
            'penghuni_id' => $penghuni->id,
            'kartu_id'    => $request->kartu_id ?: null,
            'keterangan'  => $request->keterangan,
            'status'      => 'pending',
        ]);

        return back()->with('success', 'Laporan kehilangan kartu berhasil dikirim. Kartu telah dinonaktifkan sementara.');
    }

    public function bukaPintuWeb(Request $request)
    {
        $penghuni = auth()->user()->penghuni;
        if (!$penghuni || !$penghuni->kamar) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki kamar aktif.'], 403);
        }

        $kamar = $penghuni->kamar;
        
        $kamar->update([
            'perintah' => 'buka',
            'terakhir_diakses' => now()
        ]);

        \App\Models\LogAkses::create([
            'uid' => 'WEB_REMOTE',
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'status' => 'berhasil',
            'aksi' => 'masuk',
            'keterangan' => 'Akses remote via Web Dashboard (Menunggu respons alat)',
            'metode_akses' => 'web',
            'waktu' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Perintah buka pintu berhasil dikirim ke alat.']);
    }

    // ── Update Nomor HP Penghuni ─────────
    public function updateNomorHp(Request $request)
    {
        $request->validate([
            'telepon' => 'required|string|max:20',
        ]);

        $user = auth()->user();
        
        // Update di tabel users
        $user->telepon = $request->telepon;
        $user->save();

        // Update di tabel penghunis jika ada
        if ($user->penghuni) {
            $user->penghuni->telepon = $request->telepon;
            $user->penghuni->save();
        }

        return back()->with('success', 'Nomor WhatsApp Anda berhasil diperbarui.');
    }
}