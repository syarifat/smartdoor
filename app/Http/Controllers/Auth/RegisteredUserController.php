<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifikasiAkunMail;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'telepon'  => ['required', 'string', 'max:20'],
            'alamat'   => ['required', 'string'],
            'foto_ktp' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'anggota'  => ['nullable', 'array'],
            'anggota.*.nama' => ['required_with:anggota', 'nullable', 'string', 'max:100'],
            'anggota.*.hubungan' => ['required_with:anggota', 'nullable', 'string', 'max:50'],
            'anggota.*.telepon' => ['nullable', 'string', 'max:20'],
            'anggota.*.foto_ktp' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        // Upload foto KTP jika ada
        $fotoPath = null;
        if ($request->hasFile('foto_ktp')) {
            $fotoPath = $request->file('foto_ktp')->store('ktp', 'public');
        }

        // Generate verification token
        $token = Str::random(64);

        // Buat akun user dengan data pendaftaran
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'penghuni',
            'telepon'  => $request->telepon,
            'alamat'   => $request->alamat,
            'foto_ktp' => $fotoPath,
            'verification_token' => $token,
            'token_expires_at' => now()->addMinutes(60),
        ]);

        // Simpan data anggota keluarga (jika ada)
        if ($request->has('anggota') && is_array($request->anggota)) {
            foreach ($request->anggota as $i => $ag) {
                if (!empty($ag['nama']) && !empty($ag['hubungan'])) {
                    $angFotoPath = null;
                    if ($request->hasFile("anggota.{$i}.foto_ktp")) {
                        $angFotoPath = $request->file("anggota.{$i}.foto_ktp")->store('ktp_anggota', 'public');
                    }
                    $user->anggotaKeluargas()->create([
                        'nama' => $ag['nama'],
                        'hubungan' => $ag['hubungan'],
                        'telepon' => $ag['telepon'] ?? null,
                        'foto_ktp' => $angFotoPath,
                    ]);
                }
            }
        }

        event(new Registered($user));

        // Kirim email verifikasi
        $verificationUrl = route('verifikasi.email', ['token' => $token]);
        Mail::to($user->email)->send(new VerifikasiAkunMail($user->name, $verificationUrl));

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'redirect' => route('cek.email')]);
        }

        return redirect()->route('cek.email')->with('success', 'Kami telah mengirim link verifikasi ke email kamu.');
    }

    public function verifikasiEmail($token)
    {
        $user = User::where('verification_token', $token)->first();

        if (!$user) {
            return redirect()->route('register')->with('error', 'Link tidak valid.');
        }

        if (!$user->hasValidToken($token)) {
            return redirect()->route('register')->with('error', 'Link expired, daftar ulang.');
        }

        $user->email_verified_at = now();
        $user->verification_token = null;
        $user->token_expires_at = null;
        $user->save();

        return redirect()->route('login')->with('success', 'Akun berhasil diverifikasi. Silakan login.');
    }
}
