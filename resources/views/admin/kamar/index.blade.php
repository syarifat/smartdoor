@extends('layouts.app')

@section('page-title', 'Data Kamar')

@section('content')

{{-- ALERT --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card-table">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="card-header-title">
            <i class="bi bi-door-open"></i> Daftar Kamar
        </div>
        <a href="{{ route('admin.kamar.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Tambah Kamar
        </a>
    </div>

    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Nomor Kamar</th>
                <th>Status</th>
                <th>Dibuat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kamars as $i => $kamar)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>Kamar {{ $kamar->nomor_kamar }}</strong></td>
                <td>
                    @if($kamar->status === 'tersedia')
                        <span class="badge bg-success">Tersedia</span>
                    @else
                        <span class="badge bg-danger">Terisi</span>
                    @endif
                </td>
                <td>{{ $kamar->created_at->format('d M Y') }}</td>
                <td>
                    <a href="{{ route('admin.kamar.edit', $kamar) }}"
                       class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <form action="{{ route('admin.kamar.destroy', $kamar) }}"
                          method="POST" class="d-inline"
                          onsubmit="confirmSubmit(event, this, 'Yakin hapus kamar ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted py-4">
                    <i class="bi bi-inbox" style="font-size:30px"></i>
                    <br>Belum ada data kamar
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection