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
.denda-badge { display:inline-flex; align-items:center; gap:4px; font-size:11px; padding:3px 9px;
    border-radius:20px; font-weight:600; }
.modal-denda .modal-header { background: linear-gradient(135deg, #1e3a5f, #2d6a9f); color:#fff; border-radius:12px 12px 0 0; }
.modal-denda .modal-header .btn-close { filter: brightness(0) invert(1); }
.modal-denda .modal-content { border-radius:12px; border:none; box-shadow:0 10px 40px rgba(0,0,0,0.15); }
.denda-input-group { position:relative; }
.denda-input-group .currency-prefix {
    position:absolute; left:12px; top:50%; transform:translateY(-50%);
    font-weight:700; color:#6c757d; font-size:15px; z-index:5;
}
.denda-input-group input { padding-left:40px; font-size:15px; font-weight:600; }
.info-box { background: linear-gradient(135deg, #eff6ff, #dbeafe); border:1px solid #bfdbfe;
    border-radius:10px; padding:12px 16px; font-size:13px; color:#1e40af; }
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

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-3" style="border-radius:10px;">
    <i class="bi bi-x-circle-fill me-2"></i>{{ session('error') }}
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
                <th class="text-center">Denda</th>
                <th class="text-center" style="width:160px;">Aksi</th>
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
                <td style="font-size:13px; max-width:220px;">{{ $lap->keterangan }}</td>
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
                    @if($lap->denda_ditagihkan && $lap->jumlah_denda > 0)
                        <span class="denda-badge bg-danger text-white">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            Rp {{ number_format($lap->jumlah_denda, 0, ',', '.') }}
                        </span>
                        @if($lap->isDendaLunas())
                            <div class="text-success fw-bold mt-1" style="font-size:10px;"><i class="bi bi-check-circle-fill"></i> Lunas</div>
                        @else
                            <div class="text-muted mt-1" style="font-size:10px;">Belum Dibayar</div>
                        @endif
                    @elseif($lap->status === 'selesai')
                        <span class="text-muted" style="font-size:12px;">Tidak ada denda</span>
                    @else
                        <span class="text-muted" style="font-size:12px;">—</span>
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
                        {{-- Tombol buka modal denda --}}
                        <button type="button"
                            class="btn btn-sm btn-outline-success"
                            title="Selesaikan & Atur Denda"
                            data-bs-toggle="modal"
                            data-bs-target="#modalDenda{{ $lap->id }}">
                            <i class="bi bi-check-lg"></i>
                        </button>
                        @endif

                        @if($lap->status === 'selesai')
                        <div class="d-flex flex-column align-items-center gap-1">
                            <span class="text-muted" style="font-size:12px;">✓ Selesai</span>
                            @if($lap->denda_ditagihkan && !$lap->isDendaLunas())
                            <form action="{{ route('admin.laporan.batal_denda', $lap->id) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan denda ini? Tagihan denda yang dibuat akan dihapus otomatis.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Batalkan Denda" style="padding: 2px 6px; font-size: 10px; border-radius: 12px;">
                                    <i class="bi bi-x-circle"></i> Batal Denda
                                </button>
                            </form>
                            @endif
                        </div>
                        @endif
                    </div>
                </td>
            </tr>

            {{-- ===== MODAL DENDA ===== --}}
            @if($lap->status !== 'selesai')
            <div class="modal fade modal-denda" id="modalDenda{{ $lap->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title mb-0">
                                    <i class="bi bi-check-circle me-2"></i>Selesaikan Laporan
                                </h5>
                                <small style="opacity:0.8;">Laporan #{{ $lap->id }} — {{ $lap->penghuni?->nama ?? '—' }}</small>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="info-box mb-4">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                Anda dapat mengenakan <strong>denda</strong> kepada penghuni atas kehilangan kartu.
                                Jika diisi, tagihan denda akan <strong>otomatis terbuat</strong> dan tercatat di sistem tagihan.
                            </div>

                            <form action="{{ route('admin.laporan.selesai', $lap->id) }}" method="POST" id="formDenda{{ $lap->id }}">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label fw-semibold" style="font-size:14px;">
                                        <i class="bi bi-cash-coin me-1 text-danger"></i>Jumlah Denda (Opsional)
                                    </label>
                                    <div class="denda-input-group">
                                        <span class="currency-prefix">Rp</span>
                                        <input
                                            type="number"
                                            name="jumlah_denda"
                                            class="form-control form-control-lg"
                                            placeholder="0 (kosongkan jika tidak ada denda)"
                                            min="0"
                                            step="1000"
                                        >
                                    </div>
                                    <div class="form-text">
                                        Contoh: 100000 untuk Rp 100.000. Kosongkan jika tidak ada denda.
                                    </div>
                                </div>

                                <div class="d-flex gap-2 justify-content-end mt-4">
                                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">
                                        Batal
                                    </button>
                                    <button type="submit" class="btn btn-success px-4 rounded-pill fw-semibold">
                                        <i class="bi bi-check-lg me-1"></i>Selesaikan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            {{-- ===== END MODAL ===== --}}

            @empty
            <tr>
                <td colspan="8" class="text-center py-5 text-muted">
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
