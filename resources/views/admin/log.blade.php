@extends('layouts.app')

@section('page-title', 'Log Aktivitas')

@section('content')
<div class="card-table">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="card-header-title m-0">
            <i class="bi bi-journal-text"></i> Riwayat Akses Pintu Smart Door
        </div>
        <form method="GET" class="d-flex gap-2 align-items-center flex-nowrap">
            {{-- Filter Metode --}}
            <select name="metode" class="form-select form-select-sm" onchange="this.form.submit()" style="width:150px;">
                <option value="">Semua Metode</option>
                <option value="rfid" {{ request('metode') == 'rfid' ? 'selected' : '' }}>RFID</option>
                <option value="pin"  {{ request('metode') == 'pin'  ? 'selected' : '' }}>PIN</option>
                <option value="web"  {{ request('metode') == 'web'  ? 'selected' : '' }}>Web / Remote</option>
            </select>

            {{-- Filter Status --}}
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width:150px;">
                <option value="">Semua Status</option>
                <option value="berhasil" {{ request('status') == 'berhasil' ? 'selected' : '' }}>✅ Berhasil</option>
                <option value="ditolak"  {{ request('status') == 'ditolak'  ? 'selected' : '' }}>❌ Ditolak</option>
            </select>

            {{-- Tombol Reset Filter --}}
            @if(request('metode') || request('status'))
                <a href="{{ route('admin.log.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 text-nowrap">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Indikator Filter Aktif --}}
    @if(request('metode') || request('status'))
    <div class="d-flex gap-2 mb-3 flex-wrap">
        <span class="text-muted" style="font-size:13px;"><i class="bi bi-funnel-fill me-1"></i>Filter aktif:</span>
        @if(request('metode'))
            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary" style="font-size:12px;">
                Metode: {{ ucfirst(request('metode')) }}
            </span>
        @endif
        @if(request('status'))
            <span class="badge rounded-pill {{ request('status') == 'berhasil' ? 'bg-success bg-opacity-10 text-success border border-success' : 'bg-danger bg-opacity-10 text-danger border border-danger' }}" style="font-size:12px;">
                Status: {{ ucfirst(request('status')) }}
            </span>
        @endif
        <span class="text-muted" style="font-size:12px;">({{ $logs->total() }} hasil)</span>
    </div>
    @endif

    <table class="table table-hover align-middle" style="table-layout: fixed; width: 100%;">
        <colgroup>
            <col style="width: 130px;">  {{-- Waktu --}}
            <col style="width: 110px;">  {{-- Kamar --}}
            <col style="width: 160px;">  {{-- Penghuni --}}
            <col style="width: 180px;">  {{-- UID Kartu / PIN --}}
            <col style="width: 95px;">   {{-- Metode --}}
            <col style="width: 85px;">   {{-- Aksi --}}
            <col style="width: 105px;">  {{-- Status --}}
            <col>                        {{-- Keterangan (sisa lebar) --}}
        </colgroup>
        <thead class="table-light">
            <tr>
                <th>Waktu</th>
                <th>Kamar</th>
                <th>Penghuni</th>
                <th>UID Kartu / PIN</th>
                <th>Metode</th>
                <th>Aksi</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td>
                    <div class="fw-semibold">{{ $log->waktu->format('d M Y') }}</div>
                    <div class="text-muted" style="font-size: 12px;">{{ $log->waktu->format('H:i:s') }}</div>
                </td>
                <td>
                    @if($log->kamar)
                        <span class="badge bg-primary">Kamar {{ $log->kamar->nomor_kamar }}</span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    @if($log->penghuni)
                        <div class="fw-semibold">{{ $log->penghuni->nama }}</div>
                    @else
                        <span class="text-muted fst-italic">Tidak Dikenal</span>
                    @endif
                </td>
                <td style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    <code>{{ $log->uid }}</code>
                </td>
                <td>
                    @if($log->metode_akses === 'rfid')
                        <span class="badge bg-success"><i class="bi bi-credit-card-2-front"></i> RFID</span>
                    @elseif($log->metode_akses === 'pin')
                        <span class="badge bg-info text-dark"><i class="bi bi-asterisk"></i> PIN</span>
                    @elseif($log->metode_akses === 'web')
                        <span class="badge" style="background-color: #6f42c1;"><i class="bi bi-globe"></i> Web</span>
                    @else
                        <span class="badge bg-secondary">Unknown</span>
                    @endif
                </td>
                <td>
                    @if($log->aksi === 'masuk')
                        <span class="badge bg-success"><i class="bi bi-box-arrow-in-right"></i> Masuk</span>
                    @else
                        <span class="badge bg-danger"><i class="bi bi-box-arrow-right"></i> Keluar</span>
                    @endif
                </td>
                <td>
                    @if($log->status === 'berhasil')
                        <span class="text-success fw-semibold"><i class="bi bi-check-circle-fill"></i> Berhasil</span>
                    @else
                        <span class="text-danger fw-semibold"><i class="bi bi-x-circle-fill"></i> Ditolak</span>
                    @endif
                </td>
                <td style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    <small class="text-muted">{{ $log->keterangan ?? '-' }}</small>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted py-5">
                    <i class="bi bi-inbox" style="font-size:40px"></i>
                    <p class="mt-2">
                        @if(request('metode') || request('status'))
                            Tidak ada data yang cocok dengan filter yang dipilih.
                        @else
                            Belum ada aktivitas tercatat.
                        @endif
                    </p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $logs->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection