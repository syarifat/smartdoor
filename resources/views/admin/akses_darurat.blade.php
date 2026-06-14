@extends('layouts.app')

@section('page-title', 'Akses Darurat')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
            <div class="card-header bg-danger text-white p-4 text-center">
                <i class="bi bi-exclamation-triangle-fill" style="font-size: 48px;"></i>
                <h3 class="fw-bold mt-2 mb-0">PERINGATAN AKSES DARURAT</h3>
            </div>
            <div class="card-body p-5 text-center">
                <h5 class="fw-bold text-dark mb-4">Membuka Seluruh Pintu Kamar Secara Bersamaan</h5>
                <p class="text-muted mb-4" style="font-size: 15px; line-height: 1.6;">
                    Fitur ini digunakan <strong>hanya pada saat kondisi darurat</strong> (seperti kebakaran, gempa bumi, atau keperluan evakuasi). 
                    Menekan tombol di bawah ini akan memerintahkan sistem agar <strong>kunci di seluruh pintu kamar</strong> terbuka secara serentak pada saat yang bersamaan.
                </p>
                <div class="alert alert-warning text-start mb-5" style="border-radius: 12px;">
                    <ul class="mb-0">
                        <li>Semua pintu kamar yang terdaftar akan langsung terbuka.</li>
                        <li>Aktivitas ini akan dicatat secara otomatis ke dalam <strong>Log Aktivitas</strong>.</li>
                        <li>Harap pastikan Anda benar-benar dalam situasi darurat sebelum menggunakan fitur ini.</li>
                    </ul>
                </div>

                <form action="{{ route('admin.akses_darurat.buka_semua') }}" method="POST" onsubmit="confirmSubmit(event, this, 'PERINGATAN DARURAT: Apakah Anda yakin ingin membuka SEMUA pintu kamar sekarang? Pastikan tindakan ini benar.')">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-lg px-5 py-3 fw-bold rounded-pill shadow" style="font-size: 18px;">
                        <i class="bi bi-unlock-fill me-2"></i> BUKA SEMUA PINTU SEKARANG
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
