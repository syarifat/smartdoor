<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tagihan;
use App\Models\Penghuni;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Auth;

class TagihanController extends Controller
{
    public function index(Request $request)
    {
        $query = Tagihan::with(['penghuni', 'kamar'])->orderBy('tanggal_tagihan', 'desc');

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $tagihans = $query->get();
        return view('admin.tagihan.index', compact('tagihans'));
    }

    public function buat()
    {
        $penghunis = Penghuni::with('kamar')->whereNotNull('kamar_id')->get();
        return view('admin.tagihan.buat', compact('penghunis'));
    }

    public function simpan(Request $request)
    {
        $request->validate([
            'penghuni_id' => 'required|exists:penghunis,id',
            'bulan' => 'required|string|max:255',
            'jumlah_tagihan' => 'required|numeric|min:0',
            'tanggal_tagihan' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        $penghuni = Penghuni::findOrFail($request->penghuni_id);

        Tagihan::create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $penghuni->kamar_id,
            'bulan' => $request->bulan,
            'jumlah_tagihan' => $request->jumlah_tagihan,
            'tanggal_tagihan' => $request->tanggal_tagihan,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('admin.tagihan.index')->with('success', 'Tagihan berhasil dibuat.');
    }

    public function verifikasi($id)
    {
        $tagihan = Tagihan::findOrFail($id);
        $tagihan->update([
            'status' => 'lunas',
            'tanggal_bayar' => now(),
        ]);



        return redirect()->back()->with('success', 'Tagihan berhasil diverifikasi manual.');
    }

    public function hapus($id)
    {
        $tagihan = Tagihan::findOrFail($id);
        $tagihan->delete();

        return redirect()->back()->with('success', 'Tagihan berhasil dihapus.');
    }
}
