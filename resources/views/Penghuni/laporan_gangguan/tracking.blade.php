@extends('layouts.app')
@section('page-title', 'Detail Laporan Gangguan')
@section('content')
<style>
.tracking-card { background:#fff; border-radius:14px; padding:28px; box-shadow:0 2px 12px rgba(0,0,0,0.07); }
.timeline { position:relative; padding-left:28px; margin-top:20px; }
.timeline::before { content:''; position:absolute; left:10px; top:0; bottom:0; width:2px; background:#e9ecef; }
.timeline-item { position:relative; margin-bottom:20px; }
.timeline-dot { position:absolute; left:-22px; width:14px; height:14px; border-radius:50%; border:2px solid #fff; box-shadow:0 0 0 2px #dee2e6; }
.timeline-dot.active { box-shadow:0 0 0 2px #2d6a9f; }
.timeline-content { background:#f8f9fa; border-radius:10px; padding:12px 16px; }
.timeline-title { font-weight:700; color:#1e3a5f; margin-bottom:2px; }
.timeline-sub { color:#888; font-size:11px; }
.timeline-note { margin-top:8px; font-size:12px; color:#555; background:#fff; padding:8px 12px; border-radius:8px; border-left:3px solid #ffc107; }
</style>

<div class="mb-4 d-flex align-items-center gap-3">
    <a href="{{ route('penghuni.laporan-gangguan.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0 fw-bold" style="color:#1e3a5f;">Detail Laporan</h5>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-md-8">

            @if($laporan)
            <div class="tracking-card">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                    <div>
                        <div class="fw-bold" style="font-size:18px; color:#1e3a5f;">{{ $laporan->no_laporan }}</div>
                        <div class="text-muted" style="font-size:13px;">Kategori: {{ $laporan->kategori }}</div>
                    </div>
                    @if($laporan->status === 'baru')
                        <span style="background:#fde8e8; color:#dc3545; padding:5px 14px; border-radius:20px; font-size:13px; font-weight:700;">Baru</span>
                    @elseif($laporan->status === 'diproses')
                        <span style="background:#fff3cd; color:#856404; padding:5px 14px; border-radius:20px; font-size:13px; font-weight:700;">Diproses</span>
                    @else
                        <span style="background:#d1f0da; color:#157347; padding:5px 14px; border-radius:20px; font-size:13px; font-weight:700;">Selesai</span>
                    @endif
                </div>

                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-dot" style="background:#dc3545;"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Laporan Diterima</div>
                            <div class="timeline-sub">{{ $laporan->created_at->format('d/m/Y H:i') }} WIB</div>
                        </div>
                    </div>
                    
                    @if(in_array($laporan->status, ['diproses', 'selesai']))
                    <div class="timeline-item">
                        <div class="timeline-dot {{ $laporan->status === 'diproses' ? 'active' : '' }}" style="background:#ffc107;"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Sedang Diproses</div>
                            <div class="timeline-sub">Laporan Anda sedang ditangani oleh admin/teknisi.</div>
                            @if($laporan->status === 'diproses' && $laporan->catatan_admin)
                                <div class="timeline-note">{{ $laporan->catatan_admin }}</div>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($laporan->status === 'selesai')
                    <div class="timeline-item">
                        <div class="timeline-dot active" style="background:#28a745;"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Selesai Ditangani</div>
                            <div class="timeline-sub">{{ $laporan->updated_at->format('d/m/Y H:i') }} WIB</div>
                            @if($laporan->catatan_admin)
                                <div class="timeline-note">{{ $laporan->catatan_admin }}</div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @else
            <div class="alert alert-danger" style="border-radius:12px;">
                <i class="bi bi-exclamation-triangle me-2"></i>Laporan dengan nomor <strong>{{ request('no_laporan') }}</strong> tidak ditemukan. Pastikan format nomor benar (KMR-XX-XXX).
            </div>
            @endif
    </div>
</div>
@endsection
