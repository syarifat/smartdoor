@extends('layouts.app')

@section('page-title', 'Dashboard Penghuni')

@section('content')

<div class="row g-4 mb-4">

    <div class="col-md-3">
        <div class="stat-card blue">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Nomor Kamar</div>
                    <div class="stat-value">{{ $nomorKamar ?? '-' }}</div>
                </div>
                <div class="stat-icon blue"><i class="bi bi-door-open"></i></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card green">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Status Pintu</div>
                    <div class="stat-value" style="font-size:18px; margin-top:5px;">
                        @if($kamar && $kamar->status_pintu == 'terbuka')
                            <span class="badge-open" style="background:#e8f5ea; color:#28a745; padding:5px 10px; border-radius:5px; font-weight:600;"><i class="bi bi-unlock"></i> Terbuka</span>
                        @else
                            <span class="badge-closed" style="background:#fde8e8; color:#dc3545; padding:5px 10px; border-radius:5px; font-weight:600;"><i class="bi bi-lock"></i> Tertutup</span>
                        @endif
                    </div>
                </div>
                <div class="stat-icon green"><i class="bi bi-unlock"></i></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card orange">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Akses Hari Ini</div>
                    <div class="stat-value">{{ $aksesHariIni }}</div>
                </div>
                <div class="stat-icon orange"><i class="bi bi-key"></i></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card purple" style="border-left-color: #6f42c1;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">PIN Kamar</div>
                    <div class="stat-value" style="font-size:18px; margin-top:5px;">
                        @php $penghuni = auth()->user()->penghuni; @endphp
                        @if($penghuni && $penghuni->pin)
                            <span style="font-family: monospace; font-size: 20px; letter-spacing: 2px;">******</span>
                        @else
                            <span style="font-size: 14px; font-weight: normal; color: #666;">Belum diatur</span>
                        @endif
                    </div>
                </div>
                <div class="stat-icon purple" style="background: #f3e8fd; color: #6f42c1;"><i class="bi bi-asterisk"></i></div>
            </div>
            <div class="mt-2 text-muted" style="font-size: 11px;">
                @if($penghuni && $penghuni->pin)
                    @if($penghuni->pin_aktif)
                        <span class="text-success fw-bold"><i class="bi bi-check-circle"></i> Status: Aktif</span>
                    @else
                        <span class="text-danger fw-bold"><i class="bi bi-x-circle"></i> Status: Nonaktif</span>
                    @endif
                @else
                    Hubungi admin untuk set PIN
                @endif
            </div>
        </div>
    </div>

</div>

{{-- RIWAYAT AKSES --}}
<div class="card-table">
    <div class="card-header-title">
        <i class="bi bi-clock-history"></i> Riwayat Akses Pintu Saya
    </div>
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Jenis Akses</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riwayatAkses as $log)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    <div class="fw-semibold">{{ \Carbon\Carbon::parse($log->waktu)->format('d M Y') }}</div>
                </td>
                <td>
                    <div class="text-muted" style="font-size: 13px;">{{ \Carbon\Carbon::parse($log->waktu)->format('H:i:s') }}</div>
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
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted py-4">
                    <i class="bi bi-inbox" style="font-size:30px"></i>
                    <br>Belum ada riwayat akses
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection