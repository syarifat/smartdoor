@extends('layouts.app')

@section('page-title', 'Laporan Kehilangan Kartu')

@section('content')

<style>
.card-table { background:#fff; border-radius:14px; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,0.06); }
.page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
.page-header h5 { font-weight:700; color:#1e3a5f; margin:0; display:flex; align-items:center; gap:10px; }
.header-icon { width:42px; height:42px; background:linear-gradient(135deg,#fde8e8,#fbc4c4); border-radius:10px;
    display:flex; align-items:center; justify-content:center; color:#dc3545; font-size:20px; }
.badge-status { font-size:12px; padding:5px 12px; border-radius:20px; font-weight:600; }
</style>

<div class="page-header">
    <h5>
        <div class="header-icon"><i class="bi bi-credit-card-2-back"></i></div>
        Laporan Kehilangan Kartu
    </h5>
    <span class="text-muted" style="font-size:13px;">
        Total: <strong>{{ $laporan->total() }}</strong> laporan
    </span>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" style="border-radius:10px;">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card-table">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th style="width:40px;">#</th>
                <th>Waktu</th>
                <th>Penghuni</th>
                <th>Kartu (UID)</th>
                <th>Keterangan</th>
                <th class="text-center">Status</th>
                <th class="text-center" style="width:140px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporan as $i => $lap)
            <tr>
                <td class="text-muted">{{ ($laporan->currentPage()-1)*$laporan->perPage()+$i+1 }}</td>
                <td>
                    <div style="font-size:13px;font-weight:600;">{{ $lap->created_at->format('d/m/Y') }}</div>
                    <div class="text-muted" style="font-size:11px;">{{ $lap->created_at->diffForHumans() }}</div>
                </td>
                <td>
                    <div class="fw-semibold" style="font-size:13px;">{{ $lap->penghuni?->nama ?? '—' }}</div>
                    <div class="text-muted" style="font-size:11px;">{{ $lap->penghuni?->user?->email ?? '' }}</div>
                </td>
                <td>
                    @if($lap->kartu)
                        <code style="background:#f1f5f9;padding:2px 7px;border-radius:5px;font-size:12px;">
                            {{ $lap->kartu->uid }}
                        </code>
                        <div class="text-muted" style="font-size:10px;">
                            Status kartu: {{ $lap->kartu->status }}
                        </div>
                    @else
                        <span class="text-muted small">—</span>
                    @endif
                </td>
                <td style="font-size:13px; max-width:240px;">{{ $lap->keterangan }}</td>
                <td class="text-center">
                    @if($lap->status === 'pending')
                        <span class="badge badge-status bg-warning text-dark">
                            <i class="bi bi-hourglass-split me-1"></i>Pending
                        </span>
                    @elseif($lap->status === 'diproses')
                        <span class="badge badge-status bg-primary">
                            <i class="bi bi-arrow-repeat me-1"></i>Diproses
                        </span>
                    @else
                        <span class="badge badge-status bg-success">
                            <i class="bi bi-check-circle me-1"></i>Selesai
                        </span>
                    @endif
                </td>
                <td class="text-center">
                    <div class="d-flex gap-1 justify-content-center">
                        @if($lap->status === 'pending')
                        <form action="{{ route('admin.laporan.proses', $lap->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Tandai Diproses">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                        </form>
                        @endif
                        @if($lap->status !== 'selesai')
                        <form action="{{ route('admin.laporan.selesai', $lap->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline-success" title="Tandai Selesai">
                                <i class="bi bi-check-lg"></i>
                            </button>
                        </form>
                        @endif
                        @if($lap->status === 'selesai')
                        <span class="text-muted" style="font-size:12px;">✓ Selesai</span>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox" style="font-size:40px;"></i>
                    <div class="mt-2">Belum ada laporan kehilangan.</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($laporan->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted" style="font-size:13px;">
            Menampilkan {{ $laporan->firstItem() }}–{{ $laporan->lastItem() }} dari {{ $laporan->total() }}
        </div>
        {{ $laporan->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection
