@extends('layouts.app')

@section('page-title', 'Tagihan & Pembayaran')

@section('content')
<div class="card-table">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="card-header-title m-0"><i class="bi bi-wallet2"></i> Data Seluruh Tagihan</h5>
        <a href="{{ route('admin.tagihan.buat') }}" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="bi bi-plus-lg"></i> Buat Tagihan
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" class="d-flex gap-2 mb-4" style="max-width: 300px;">
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="belum_bayar" {{ request('status') == 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
            <option value="menunggu_verifikasi" {{ request('status') == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
            <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
        </select>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Penghuni</th>
                    <th>Kamar</th>
                    <th>Bulan</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tagihans as $t)
                <tr>
                    <td class="fw-bold">{{ $t->penghuni->nama ?? '-' }}</td>
                    <td>Kamar {{ $t->kamar->nomor_kamar ?? '-' }}</td>
                    <td>{{ $t->bulan }}</td>
                    <td>Rp {{ number_format($t->jumlah_tagihan, 0, ',', '.') }}</td>
                    <td>
                        @if($t->status == 'belum_bayar')
                            <span class="badge bg-danger">Belum Bayar</span>
                        @elseif($t->status == 'menunggu_verifikasi')
                            <span class="badge bg-warning text-dark">Menunggu Verifikasi</span>
                        @else
                            <span class="badge bg-success">Lunas</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            @if($t->bukti_pembayaran)
                                <button type="button" class="btn btn-sm btn-info text-white" title="Lihat Bukti Transfer" data-bs-toggle="modal" data-bs-target="#buktiModal{{ $t->id }}">
                                    <i class="bi bi-card-image"></i>
                                </button>
                                
                                <!-- Modal Lihat Bukti -->
                                <div class="modal fade" id="buktiModal{{ $t->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Bukti Pembayaran - {{ $t->penghuni->nama ?? '-' }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img src="{{ asset('storage/' . str_replace('\\', '/', $t->bukti_pembayaran)) }}" class="img-fluid rounded shadow-sm" alt="Bukti Pembayaran">
                                            </div>
                                            <div class="modal-footer justify-content-center">
                                                @if($t->status == 'menunggu_verifikasi')
                                                <form action="{{ route('admin.tagihan.verifikasi', $t->id) }}" method="POST" class="w-100">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="btn btn-success w-100 fw-bold">Verifikasi Lunas</button>
                                                </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($t->status == 'menunggu_verifikasi')
                                <form action="{{ route('admin.tagihan.verifikasi', $t->id) }}" method="POST" onsubmit="confirmSubmit(event, this, 'Verifikasi manual pembayaran ini sebagai Lunas?');">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-success" title="Verifikasi Lunas">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('admin.tagihan.hapus', $t->id) }}" method="POST" onsubmit="confirmSubmit(event, this, 'Hapus tagihan ini?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Belum ada data tagihan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
