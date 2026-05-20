@extends('layouts.app')

@section('page-title', 'Manajemen Kartu RFID')

@section('content')
<div class="card-table">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="card-header-title mb-0"><i class="bi bi-credit-card"></i> Daftar Kartu RFID</h5>
        <a href="{{ route('admin.kartu.create') }}" class="btn btn-primary btn-sm px-3 rounded-pill" style="background:#2d6a9f; border:none;">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kartu Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius:10px;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th class="text-center" width="50">#</th>
                    <th>UID Kartu</th>
                    <th>Pemilik (Penghuni)</th>
                    <th>Status</th>
                    <th class="text-center" width="150">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kartus as $kartu)
                <tr>
                    <td class="text-center text-muted">{{ $loop->iteration }}</td>
                    <td class="fw-bold" style="color:#2d6a9f; font-family: monospace; font-size:15px;">{{ $kartu->uid }}</td>
                    <td>
                        @if($kartu->penghuni)
                            <div class="fw-semibold text-dark">{{ $kartu->penghuni->nama }}</div>
                            <small class="text-muted">Kamar: {{ $kartu->penghuni->kamar ? $kartu->penghuni->kamar->nomor_kamar : '-' }}</small>
                        @else
                            <span class="badge bg-secondary">Belum Ditugaskan</span>
                        @endif
                    </td>
                    <td>
                        @if($kartu->status === 'aktif')
                            <span class="badge bg-success rounded-pill px-3">Aktif</span>
                        @elseif($kartu->status === 'nonaktif')
                            <span class="badge bg-warning text-dark rounded-pill px-3">Nonaktif</span>
                        @else
                            <span class="badge bg-danger rounded-pill px-3">Hilang</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('admin.kartu.edit', $kartu->id) }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px;">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('admin.kartu.destroy', $kartu->id) }}" method="POST" onsubmit="confirmSubmit(event, this, 'Yakin ingin menghapus kartu ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius:8px;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="bi bi-credit-card-2-front" style="font-size:30px; color:#ccc;"></i><br>
                        Belum ada data Kartu RFID.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
