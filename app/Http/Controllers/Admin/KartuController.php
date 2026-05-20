<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kartu;
use App\Models\Penghuni;
use Illuminate\Http\Request;

class KartuController extends Controller
{
    public function index()
    {
        $kartus = Kartu::with('penghuni')->get();
        return view('admin.kartu.index', compact('kartus'));
    }

    public function create()
    {
        $penghunis = Penghuni::orderBy('nama')->get();
        return view('admin.kartu.create', compact('penghunis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'uid' => 'required|unique:kartu,uid',
            'penghuni_id' => 'nullable|exists:penghunis,id',
            'status' => 'required|in:aktif,nonaktif,hilang'
        ]);

        Kartu::create($request->all());

        return redirect()->route('admin.kartu.index')->with('success', 'Kartu RFID berhasil ditambahkan!');
    }

    public function edit(Kartu $kartu)
    {
        $penghunis = Penghuni::orderBy('nama')->get();
        return view('admin.kartu.edit', compact('kartu', 'penghunis'));
    }

    public function update(Request $request, Kartu $kartu)
    {
        $request->validate([
            'uid' => 'required|unique:kartu,uid,' . $kartu->id,
            'penghuni_id' => 'nullable|exists:penghunis,id',
            'status' => 'required|in:aktif,nonaktif,hilang'
        ]);

        $kartu->update($request->all());

        return redirect()->route('admin.kartu.index')->with('success', 'Data Kartu RFID berhasil diperbarui!');
    }

    public function destroy(Kartu $kartu)
    {
        $kartu->delete();

        return redirect()->route('admin.kartu.index')->with('success', 'Kartu RFID berhasil dihapus!');
    }
}
