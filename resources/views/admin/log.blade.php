@extends('layouts.app')

@section('page-title', 'Log Aktivitas')

@section('content')
<div class="card-table">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="card-header-title m-0">
            <i class="bi bi-journal-text"></i> Riwayat Akses Pintu Smart Door
        </div>
        <form method="GET" class="d-flex gap-2">
            <select name="metode" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Metode</option>
                <option value="rfid" {{ request('metode') == 'rfid' ? 'selected' : '' }}>RFID</option>
                <option value="pin" {{ request('metode') == 'pin' ? 'selected' : '' }}>PIN</option>
                <option value="web" {{ request('metode') == 'web' ? 'selected' : '' }}>Web / Remote</option>
            </select>
        </form>
    </div>

    <table class="table table-hover align-middle">
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
                <td>
                    @if($log->penghuni)
                        <div class="fw-semibold">{{ $log->penghuni->nama }}</div>
                    @else
                        <span class="text-muted fst-italic">Tidak Dikenal</span>
                    @endif
                </td>
                <td><code>{{ $log->uid }}</code></td>
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
                        <span class="text-success"><i class="bi bi-check-circle-fill"></i> Berhasil</span>
                    @else
                        <span class="text-danger"><i class="bi bi-x-circle-fill"></i> Ditolak</span>
                    @endif
                </td>
                <td><small class="text-muted">{{ $log->keterangan ?? '-' }}</small></td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-5">
                    <i class="bi bi-inbox" style="font-size:40px"></i>
                    <p class="mt-2">Belum ada aktivitas tercatat.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $logs->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection