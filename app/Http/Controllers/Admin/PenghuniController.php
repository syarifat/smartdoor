<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penghuni;
use App\Models\Kamar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PenghuniController extends Controller
{
    public function index()
    {
        $penghunis = Penghuni::with(['kamar', 'user'])->orderBy('nama')->get();
        return view('admin.penghuni.index', compact('penghunis'));
    }

    public function create(Request $request)
    {
        $kamars = Kamar::where('status', 'tersedia')->orderBy('nomor_kamar')->get();

        // Jika datang dari tombol "Proses" di dashboard, pre-fill data user
        $prefilledUser = null;
        if ($request->filled('user_id')) {
            $prefilledUser = \App\Models\User::with('anggotaKeluargas')->find($request->user_id);
        }

        return view('admin.penghuni.create', compact('kamars', 'prefilledUser'));
    }

    /**
     * AJAX endpoint: cari penghuni yang sudah registrasi (belum punya kamar)
     * GET /admin/penghuni/search?q=budi
     */
    public function search(Request $request)
    {
        $q = $request->query('q', '');

        $results = \App\Models\User::with('anggotaKeluargas')
            ->where('role', 'penghuni')
            ->doesntHave('penghuni')
            ->where('name', 'like', "%{$q}%")
            ->limit(10)
            ->get()
            ->map(function ($u) {
                return [
                    'id'       => $u->id,
                    'nama'     => $u->name,
                    'telepon'  => $u->telepon ?? '',
                    'alamat'   => $u->alamat ?? '',
                    'email'    => $u->email ?? '',
                    'foto_ktp' => $u->foto_ktp
                                    ? asset('storage/' . str_replace('\\', '/', $u->foto_ktp))
                                    : null,
                    'anggota'  => $u->anggotaKeluargas->map(function ($a) {
                        return [
                            'nama' => $a->nama,
                            'hubungan' => $a->hubungan,
                            'telepon' => $a->telepon,
                            'foto_ktp' => $a->foto_ktp ? asset('storage/' . str_replace('\\', '/', $a->foto_ktp)) : null
                        ];
                    })
                ];
            });

        return response()->json($results);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'     => 'required|max:100',
            'telepon'  => 'nullable|max:20',
            'alamat'   => 'nullable',
            'kamar_id' => 'nullable|exists:kamars,id',
            'foto_ktp' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only('nama', 'telepon', 'alamat', 'kamar_id');

        // Jika admin memilih dari autocomplete, link ke user yang ada
        if ($request->filled('user_id')) {
            $data['user_id'] = $request->user_id;
            
            // Bila admin mengganti foto, simpan foto baru
            if ($request->hasFile('foto_ktp')) {
                $data['foto_ktp'] = $request->file('foto_ktp')->store('ktp', 'public');
            } else {
                // Ambil foto dari user table
                $user = \App\Models\User::find($request->user_id);
                $data['foto_ktp'] = $user->foto_ktp;
            }

            $penghuni = Penghuni::create($data);
            
            // Sinkronisasi Anggota Keluarga
            \App\Models\AnggotaKeluarga::where('user_id', $request->user_id)
                ->update(['penghuni_id' => $penghuni->id]);

        } else {
            // Admin tambah manual (penghuni belum punya akun / bukan dari registrasi)
            if ($request->hasFile('foto_ktp')) {
                $data['foto_ktp'] = $request->file('foto_ktp')->store('ktp', 'public');
            }
            $penghuni = Penghuni::create($data);
        }

        if ($request->kamar_id) {
            Kamar::find($request->kamar_id)->update(['status' => 'terisi']);
        }

        return redirect()->route('admin.penghuni.index')
                         ->with('success', 'Data penghuni berhasil disimpan!');
    }

    public function edit(Penghuni $penghuni)
    {
        $kamars = Kamar::orderBy('nomor_kamar')->get();
        return view('admin.penghuni.edit', compact('penghuni', 'kamars'));
    }

    public function update(Request $request, Penghuni $penghuni)
    {
        $request->validate([
            'nama'     => 'required|max:100',
            'telepon'  => 'nullable|max:20',
            'alamat'   => 'nullable',
            'kamar_id' => 'nullable|exists:kamars,id',
            'foto_ktp' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($penghuni->kamar_id && $penghuni->kamar_id != $request->kamar_id) {
            Kamar::find($penghuni->kamar_id)->update(['status' => 'tersedia']);
        }

        $data = $request->only('nama', 'telepon', 'alamat', 'kamar_id');

        if ($request->hasFile('foto_ktp')) {
            if ($penghuni->foto_ktp) Storage::disk('public')->delete($penghuni->foto_ktp);
            $data['foto_ktp'] = $request->file('foto_ktp')->store('ktp', 'public');
        }

        $penghuni->update($data);

        if ($request->kamar_id) {
            Kamar::find($request->kamar_id)->update(['status' => 'terisi']);
        }

        return redirect()->route('admin.penghuni.index')
                         ->with('success', 'Data penghuni berhasil diupdate!');
    }

    public function destroy(Penghuni $penghuni)
    {
        if ($penghuni->kamar_id) {
            Kamar::find($penghuni->kamar_id)->update(['status' => 'tersedia']);
        }

        if ($penghuni->foto_ktp) {
            Storage::disk('public')->delete($penghuni->foto_ktp);
        }

        $userId = $penghuni->user_id;

        $penghuni->delete();

        // Hapus akun user yang terkait agar email bisa didaftarkan ulang
        if ($userId) {
            $user = \App\Models\User::with('anggotaKeluargas')->find($userId);
            if ($user && $user->role === 'penghuni') {
                foreach ($user->anggotaKeluargas as $anggota) {
                    if ($anggota->foto_ktp) {
                        Storage::disk('public')->delete($anggota->foto_ktp);
                    }
                }
                $user->delete();
            }
        }

        return redirect()->route('admin.penghuni.index')
                         ->with('success', 'penghuni sudah dihapus');
    }

    public function setPinPenghuni(Request $request, $id)
    {
        $request->validate([
            'pin' => 'required|digits:6',
        ]);

        $penghuni = Penghuni::findOrFail($id);
        $penghuni->update([
            'pin' => \Illuminate\Support\Facades\Hash::make($request->pin),
        ]);

        return redirect()->back()->with('success', 'PIN darurat berhasil diatur!');
    }

    public function togglePinAktif($id)
    {
        $penghuni = Penghuni::findOrFail($id);
        $penghuni->update([
            'pin_aktif' => !$penghuni->pin_aktif,
        ]);

        $status = $penghuni->pin_aktif ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "PIN darurat berhasil $status.");
    }
}