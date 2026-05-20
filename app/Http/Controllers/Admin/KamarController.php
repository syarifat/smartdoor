<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use Illuminate\Http\Request;

class KamarController extends Controller
{
    public function index()
    {
        $kamars = Kamar::orderBy('nomor_kamar')->get();
        return view('admin.kamar.index', compact('kamars'));
    }

    public function create()
    {
        return view('admin.kamar.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor_kamar' => 'required|unique:kamars,nomor_kamar|max:50',
            'status'      => 'required|in:tersedia,terisi',
        ]);

        Kamar::create($request->only('nomor_kamar', 'status'));

        return redirect()->route('admin.kamar.index')
                         ->with('success', 'Kamar berhasil ditambahkan!');
    }

    public function edit(Kamar $kamar)
    {
        return view('admin.kamar.edit', compact('kamar'));
    }

    public function update(Request $request, Kamar $kamar)
    {
        $request->validate([
            'nomor_kamar' => 'required|unique:kamars,nomor_kamar,'.$kamar->id.'|max:50',
            'status'      => 'required|in:tersedia,terisi',
        ]);

        $kamar->update($request->only('nomor_kamar', 'status'));

        return redirect()->route('admin.kamar.index')
                         ->with('success', 'Kamar berhasil diupdate!');
    }

    public function destroy(Kamar $kamar)
    {
        $kamar->delete();

        return redirect()->route('admin.kamar.index')
                         ->with('success', 'Kamar berhasil dihapus!');
    }
}