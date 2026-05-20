@extends('layouts.app')

@section('page-title', 'Percobaan Akses Tidak Sah')

@section('content')

<style>
.snap-thumb {
    width: 72px;
    height: 54px;
    object-fit: cover;
    border-radius: 6px;
    cursor: pointer;
    border: 2px solid #dee2e6;
    transition: border-color 0.2s, transform 0.2s;
}
.snap-thumb:hover {
    border-color: #dc3545;
    transform: scale(1.06);
}
.badge-attempt {
    font-size: 13px;
    font-weight: 700;
    padding: 5px 11px;
    border-radius: 20px;
}
.row-new {
    background: #fff8f8 !important;
}
.card-table {
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
}
.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}
.page-header h5 {
    font-weight: 700;
    color: #1e3a5f;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.page-header .header-icon {
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, #fde8e8, #fbc4c4);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #dc3545;
    font-size: 20px;
}
/* Modal foto fullsize */
#modalFoto .modal-content {
    background: #111;
    border: none;
    border-radius: 12px;
}
#modalFoto img {
    max-height: 80vh;
    width: 100%;
    object-fit: contain;
    border-radius: 8px;
}
#modalFoto .modal-header {
    background: #1a1a1a;
    border-bottom: 1px solid #333;
    border-radius: 12px 12px 0 0;
}
#modalFoto .modal-title { color: #fff; font-size: 14px; }
#modalFoto .btn-close { filter: invert(1); }
</style>

<div class="page-header">
    <h5>
        <div class="header-icon"><i class="bi bi-shield-exclamation"></i></div>
        Percobaan Akses Tidak Sah
    </h5>
    <span class="text-muted" style="font-size:13px;">
        <i class="bi bi-info-circle me-1"></i>
        Semua record otomatis ditandai sudah dilihat saat halaman ini dibuka.
    </span>
</div>

{{-- Flash message --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert" style="border-radius:10px;">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card-table">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th style="width:44px;">#</th>
                <th>Waktu</th>
                <th>Kamar</th>
                <th>UID Kartu</th>
                <th class="text-center">Percobaan</th>
                <th class="text-center">Foto Snapshot</th>
                <th class="text-center" style="width:80px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $i => $rec)
            <tr>
                <td class="text-muted">{{ ($data->currentPage() - 1) * $data->perPage() + $i + 1 }}</td>
                <td>
                    <div style="font-size:13px; font-weight:600;">
                        {{ $rec->waktu->format('d/m/Y') }}
                    </div>
                    <div class="text-muted" style="font-size:11px;">
                        {{ $rec->waktu->format('H:i:s') }}
                        &bull; {{ $rec->waktu->diffForHumans() }}
                    </div>
                </td>
                <td>
                    @if($rec->kamar)
                        <span class="badge bg-primary">Kamar {{ $rec->kamar->nomor_kamar }}</span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>
                    <code style="background:#f1f5f9; padding:3px 8px; border-radius:5px; font-size:12px;">
                        {{ $rec->rfid_uid }}
                    </code>
                </td>
                <td class="text-center">
                    @php
                        $badgeClass = $rec->jumlah_percobaan >= 3 ? 'bg-danger' : ($rec->jumlah_percobaan == 2 ? 'bg-warning text-dark' : 'bg-secondary');
                    @endphp
                    <span class="badge badge-attempt {{ $badgeClass }}">
                        {{ $rec->jumlah_percobaan }}x
                    </span>
                </td>
                <td class="text-center">
                    @if($rec->foto_path)
                        <img src="{{ asset('storage/' . $rec->foto_path) }}"
                             alt="Snapshot"
                             class="snap-thumb"
                             data-foto="{{ asset('storage/' . $rec->foto_path) }}"
                             data-kamar="{{ $rec->kamar ? 'Kamar ' . $rec->kamar->nomor_kamar : '-' }}"
                             data-waktu="{{ $rec->waktu->format('d/m/Y H:i:s') }}"
                             onclick="lihatFoto(this)">
                    @else
                        <span class="text-muted" style="font-size:12px;">
                            <i class="bi bi-camera-slash"></i> Tidak ada foto
                        </span>
                    @endif
                </td>
                <td class="text-center">
                    <form action="{{ route('admin.percobaan.destroy', $rec->id) }}" method="POST"
                          onsubmit="confirmSubmit(event, this, 'Hapus record ini beserta fotonya?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                    <i class="bi bi-shield-check" style="font-size: 48px; color: #22c55e;"></i>
                    <div class="mt-2" style="font-size:15px; font-weight:600; color:#22c55e;">
                        Tidak ada percobaan akses tidak sah!
                    </div>
                    <div style="font-size:13px;">Sistem aman — semua akses berjalan normal.</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($data->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted" style="font-size:13px;">
            Menampilkan {{ $data->firstItem() }}–{{ $data->lastItem() }} dari {{ $data->total() }} record
        </div>
        {{ $data->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- Modal Foto Fullsize --}}
<div class="modal fade" id="modalFoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">
                    <i class="bi bi-camera-fill me-2 text-danger"></i>
                    <span id="modalFotoLabel">Snapshot – </span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="modalFotoImg" src="" alt="Snapshot Percobaan Gagal">
            </div>
        </div>
    </div>
</div>

<script>
function lihatFoto(el) {
    document.getElementById('modalFotoImg').src   = el.dataset.foto;
    document.getElementById('modalFotoLabel').textContent =
        'Snapshot – ' + el.dataset.kamar + ' · ' + el.dataset.waktu;
    new bootstrap.Modal(document.getElementById('modalFoto')).show();
}
</script>

@endsection
