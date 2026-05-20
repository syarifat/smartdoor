@extends('layouts.app')

@section('page-title', 'Lapor Kehilangan Kartu')

@section('content')

<style>
.form-card {
    background: #fff;
    border-radius: 14px;
    padding: 28px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    border-left: 4px solid #dc3545;
    margin-bottom: 24px;
}
.riwayat-card {
    background: #fff;
    border-radius: 14px;
    padding: 24px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
}
.page-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
}
.header-icon {
    width: 46px; height: 46px;
    background: linear-gradient(135deg,#fde8e8,#fbc4c4);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    color: #dc3545; font-size: 22px;
}
.page-header h5 { font-weight: 700; color: #1e3a5f; margin: 0; }
.badge-status { font-size: 12px; padding: 5px 12px; border-radius: 20px; }
</style>

<div class="page-header">
    <div class="header-icon"><i class="bi bi-credit-card-2-back"></i></div>
    <div>
        <h5>Lapor Kehilangan Kartu</h5>
        <div class="text-muted" style="font-size:13px;">Laporkan jika kartu RFID Anda hilang atau dicuri. Kartu akan otomatis dinonaktifkan.</div>
    </div>
</div>

{{-- Flash messages --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius:10px;">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius:10px;">
    <i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- FORM LAPORAN --}}
<div class="form-card">
    <h6 class="fw-bold text-danger mb-3"><i class="bi bi-exclamation-triangle me-2"></i>Buat Laporan Baru</h6>

    @if($kartus->isEmpty())
        <div class="alert alert-warning mb-0" style="border-radius:8px; font-size:13px;">
            <i class="bi bi-info-circle me-1"></i>
            Anda belum memiliki kartu RFID terdaftar. Hubungi admin untuk mendapatkan kartu.
        </div>
    @else
    <form action="{{ route('penghuni.laporan.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Pilih Kartu yang Hilang</label>
            <select name="kartu_id" class="form-select @error('kartu_id') is-invalid @enderror">
                <option value="">— Tidak tahu / tidak ada kartu terdaftar —</option>
                @foreach($kartus as $kartu)
                <option value="{{ $kartu->id }}" {{ old('kartu_id') == $kartu->id ? 'selected' : '' }}>
                    UID: {{ $kartu->uid }}
                    ({{ $kartu->status === 'aktif' ? '✅ Aktif' : '🔴 Nonaktif' }})
                </option>
                @endforeach
            </select>
            @error('kartu_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Keterangan <span class="text-danger">*</span></label>
            <textarea name="keterangan" rows="3"
                class="form-control @error('keterangan') is-invalid @enderror"
                placeholder="Ceritakan situasi kehilangan kartu Anda (kapan, dimana, bagaimana)...">{{ old('keterangan') }}</textarea>
            @error('keterangan')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-danger px-4">
            <i class="bi bi-send me-2"></i>Kirim Laporan
        </button>
    </form>
    @endif
</div>

{{-- RIWAYAT LAPORAN --}}
<div class="riwayat-card">
    <h6 class="fw-bold" style="color:#1e3a5f; margin-bottom:16px;">
        <i class="bi bi-clock-history me-2"></i>Riwayat Laporan Saya
    </h6>
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Kartu (UID)</th>
                <th>Keterangan</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riwayat as $i => $lap)
            <tr>
                <td class="text-muted">{{ $i + 1 }}</td>
                <td>
                    <div style="font-size:13px;">{{ $lap->created_at->format('d/m/Y') }}</div>
                    <div class="text-muted" style="font-size:11px;">{{ $lap->created_at->format('H:i') }}</div>
                </td>
                <td>
                    @if($lap->kartu)
                        <code style="background:#f1f5f9;padding:2px 7px;border-radius:5px;font-size:12px;">
                            {{ $lap->kartu->uid }}
                        </code>
                    @else
                        <span class="text-muted small">—</span>
                    @endif
                </td>
                <td style="font-size:13px; max-width:260px;">{{ $lap->keterangan }}</td>
                <td class="text-center">
                    @if($lap->status === 'pending')
                        <span class="badge badge-status bg-warning text-dark">
                            <i class="bi bi-hourglass-split me-1"></i>Menunggu
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
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center py-4 text-muted">
                    <i class="bi bi-inbox" style="font-size:30px;"></i>
                    <br>Belum ada laporan yang dibuat.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
