<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isVerified()) {
            return redirect()->route('cek.email')->with('warning', 'Akun Anda belum diverifikasi.');
        }

        if (auth()->user()->role !== 'admin') {
            return redirect()->route('penghuni.dashboard');
        }

        return $next($request);
    }
}
