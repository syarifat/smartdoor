@extends('layouts.app')
@section('page-title', 'Detail Laporan – ' . $laporanGangguan->no_laporan)
@section('content')
<style>
.page-header{display:flex;align-items:center;gap:12px;margin-bottom:24px;}
.detail-card{background:#fff;border-radius:14px;padding:28px;box-shadow:0 2px 12px rgba(0,0,0,0.07);}
.info-row{display:flex;gap:8px;padding:12px 0;border-bottom:1px solid #f0f0f0;}
.info-row:last-child{border-bottom:none;}
.info-label{width:160px;min-width:160px;color:#888;font-size:13px;font-weight:600;}
.info-value{font-size:13px;color:#1e3a5f;font-weight:500;}
.badge-baru{background:#fde8e8;color:#dc3545;padding:5px 14px;border-radius:20px;font-size:13px;font-weight:700;}
.badge-diproses{background:#fff3cd;color:#856404;padding:5px 14px;border-radius:20px;font-size:13px;font-weight:700;}
.badge-selesai{background:#d1f0da;color:#157347;padding:5px 14px;border-radius:20px;font-size:13px;font-weight:700;}
.no-lp{background:#f1f5f9;padding:5px 12px;border-radius:8px;font-family:monospace;font-size:15px;color:#1e3a5f;font-weight:700;letter-spacing:1px;}
.foto-bukti{border-radius:10px;border:2px solid #e9ecef;max-width:100%;max-height:320px;object-fit:cover;}
.timeline{position:relative;padding-left:28px;}
.timeline::before{content:'';position:absolute;left:10px;top:0;bottom:0;width:2px;background:#e9ecef;}
.timeline-item{position:relative;margin-bottom:20px;}
.timeline-dot{position:absolute;left:-22px;width:14px;height:14px;border-radius:50%;border:2px solid #fff;box-shadow:0 0 0 2px #dee2e6;}
.timeline-dot.active{box-shadow:0 0 0 2px #2d6a9f;}
.timeline-content{background:#f8f9fa;border-radius:10px;padding:10px 14px;font-size:13px;}
.timeline-title{font-weight:700;color:#1e3a5f;margin-bottom:2px;}
.timeline-sub{color:#888;font-size:11px;}
</style>

<div class="page-header">
    <a href="{{ route('admin.laporan-gangguan.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
    <div>
        <h5 style="font-weight:700;color:#1e3a5f;margin:0;">Detail Laporan</h5>
        <div class="text-muted" style="font-size:13px;">Informasi lengkap laporan gangguan</div>
    </div>
</div>

<div class="row g-4">
    {{-- Info Utama --}}
    <div class="col-12 col-md-8">
        <div class="detail-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <code class="no-lp">{{ $laporanGangguan->no_laporan }}</code>
                @if($laporanGangguan->status==='baru')
                    <span class="badge-baru"><i class="bi bi-circle-fill me-1" style="font-size:8px;"></i>Baru</span>
                @elseif($laporanGangguan->status==='diproses')
                    <span class="badge-diproses"><i class="bi bi-arrow-repeat me-1"></i>Diproses</span>
                @else
                    <span class="badge-selesai"><i class="bi bi-check-circle me-1"></i>Selesai</span>
                @endif
            </div>

            <div class="info-row"><span class="info-label"><i class="bi bi-person me-1"></i>Penghuni</span><span class="info-value fw-bold">{{ $laporanGangguan->nama_penghuni }}</span></div>
            <div class="info-row"><span class="info-label"><i class="bi bi-door-open me-1"></i>No. Kamar</span><span class="info-value"><span style="background:#e8f0f8;color:#2d6a9f;padding:2px 12px;border-radius:10px;font-weight:700;">{{ $laporanGangguan->no_kamar }}</span></span></div>
            <div class="info-row">
                <span class="info-label"><i class="bi bi-tag me-1"></i>Kategori</span>
                <span class="info-value">
                    @php $katIcon = match($laporanGangguan->kategori) { 'Listrik'=>'bi-lightning-charge','Air'=>'bi-droplet','Furnitur'=>'bi-box','Pintu & Kunci'=>'bi-door-open','Internet'=>'bi-wifi',default=>'bi-three-dots' }; @endphp
                    <i class="bi {{ $katIcon }} me-1"></i>{{ $laporanGangguan->kategori }}
                </span>
            </div>

            <div class="info-row"><span class="info-label"><i class="bi bi-calendar me-1"></i>Tanggal Lapor</span><span class="info-value">{{ $laporanGangguan->created_at->format('d F Y, H:i') }} WIB</span></div>

            <div class="mt-3">
                <div class="fw-bold text-muted mb-2" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;"><i class="bi bi-chat-text me-1"></i>Deskripsi Masalah</div>
                <div style="background:#f8f9fa;border-radius:10px;padding:14px 16px;font-size:13px;color:#333;line-height:1.7;">
                    {{ $laporanGangguan->deskripsi }}
                </div>
            </div>

            @if($laporanGangguan->catatan_admin)
            <div class="mt-3">
                <div class="fw-bold text-muted mb-2" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;"><i class="bi bi-pencil-square me-1"></i>Catatan Admin</div>
                <div style="background:#fff8e1;border-radius:10px;padding:14px 16px;font-size:13px;color:#856404;border-left:3px solid #ffc107;">
                    {{ $laporanGangguan->catatan_admin }}
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Panel Kanan --}}
    <div class="col-12 col-md-4">
        {{-- Foto Bukti --}}
        @if($laporanGangguan->foto_bukti)
        <div class="detail-card mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="fw-bold" style="color:#1e3a5f;font-size:14px;"><i class="bi bi-image me-2"></i>Foto Bukti</div>
                <span class="text-muted" style="font-size:11px;"><i class="bi bi-zoom-in me-1"></i>Klik untuk perbesar</span>
            </div>
            <img src="{{ Storage::url($laporanGangguan->foto_bukti) }}"
                 alt="Foto Bukti"
                 class="foto-bukti w-100"
                 onclick="previewFoto(this.src)"
                 style="cursor:zoom-in; transition: opacity .2s;"
                 onmouseover="this.style.opacity='.85'"
                 onmouseout="this.style.opacity='1'"
            >
        </div>
        @endif

        {{-- Timeline Status --}}
        <div class="detail-card mb-4">
            <div class="fw-bold mb-3" style="color:#1e3a5f;font-size:14px;"><i class="bi bi-clock-history me-2"></i>Riwayat Status</div>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-dot" style="background:#dc3545;"></div>
                    <div class="timeline-content">
                        <div class="timeline-title">🔴 Laporan Baru Masuk</div>
                        <div class="timeline-sub">{{ $laporanGangguan->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
                @if(in_array($laporanGangguan->status, ['diproses','selesai']))
                <div class="timeline-item">
                    <div class="timeline-dot {{ $laporanGangguan->status==='diproses'?'active':'' }}" style="background:#ffc107;"></div>
                    <div class="timeline-content">
                        <div class="timeline-title">🟡 Sedang Diproses</div>
                        <div class="timeline-sub">{{ $laporanGangguan->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
                @endif
                @if($laporanGangguan->status==='selesai')
                <div class="timeline-item">
                    <div class="timeline-dot" style="background:#28a745;"></div>
                    <div class="timeline-content">
                        <div class="timeline-title">✅ Selesai Ditangani</div>
                        <div class="timeline-sub">{{ $laporanGangguan->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Aksi Admin --}}
        @if($laporanGangguan->status !== 'selesai')
        <div class="detail-card">
            <div class="fw-bold mb-3" style="color:#1e3a5f;font-size:14px;"><i class="bi bi-gear me-2"></i>Aksi</div>

            @if($laporanGangguan->status==='baru')
            <form method="POST" action="{{ route('admin.laporan-gangguan.proses', $laporanGangguan->id) }}" class="mb-2" id="formProses">
                @csrf @method('PATCH')
                <div class="mb-2">
                    <label class="form-label fw-semibold" style="font-size:12px;">Catatan (opsional)</label>
                    <textarea name="catatan_admin" class="form-control form-control-sm" rows="2" placeholder="Catatan untuk penghuni..."></textarea>
                </div>
                <button type="button" class="btn btn-warning w-100 fw-bold" onclick="konfirmasiProses()">
                    <i class="bi bi-arrow-repeat me-2"></i>Tandai Diproses
                </button>
            </form>
            @endif

            <form method="POST" action="{{ route('admin.laporan-gangguan.selesai', $laporanGangguan->id) }}" id="formSelesai">
                @csrf @method('PATCH')
                <div class="mb-2">
                    <label class="form-label fw-semibold" style="font-size:12px;">Catatan Penyelesaian (opsional)</label>
                    <textarea name="catatan_admin" class="form-control form-control-sm" rows="2" placeholder="Penjelasan penyelesaian...">{{ $laporanGangguan->catatan_admin }}</textarea>
                </div>
                <button type="button" class="btn btn-success w-100 fw-bold" onclick="konfirmasiSelesai()">
                    <i class="bi bi-check-circle me-2"></i>Tandai Selesai
                </button>
            </form>
        </div>
        @endif
    </div>
</div>

{{-- Modal Preview Foto --}}
<div class="modal fade" id="modalFotoBukti" tabindex="-1" style="backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); background-color: rgba(0,0,0,0.6);">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content" style="background:transparent; border:none; box-shadow:none;">
            <div class="modal-body p-0 position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                    data-bs-dismiss="modal" style="z-index:10; background-color:rgba(0,0,0,.5); border-radius:50%; padding:10px; filter:none; opacity:1;"></button>
                <img id="fotoPreviewImg" src="" alt="Foto Bukti"
                     style="width:100%; max-height:90vh; object-fit:contain; border-radius:12px; display:block;">
                <div class="text-center mt-3">
                    <a id="fotoDownloadLink" href="" download class="btn btn-sm btn-light px-4" style="border-radius:20px;">
                        <i class="bi bi-download me-2"></i>Unduh Foto
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewFoto(src) {
    document.getElementById('fotoPreviewImg').src = src;
    document.getElementById('fotoDownloadLink').href = src;
    new bootstrap.Modal(document.getElementById('modalFotoBukti')).show();
}
function konfirmasiProses() {
    Swal.fire({
        title: 'Tandai Diproses?',
        text: 'Laporan ini akan ditandai sedang diproses. Notifikasi WA dikirim ke penghuni.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Proses!',
        cancelButtonText: 'Batal',
        position: 'center'
    }).then(r => { if (r.isConfirmed) document.getElementById('formProses').submit(); });
}
function konfirmasiSelesai() {
    Swal.fire({
        title: 'Tandai Selesai?',
        text: 'Laporan ini akan ditandai selesai. Notifikasi WA dikirim ke penghuni.',
        icon: 'success',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Selesai!',
        cancelButtonText: 'Batal',
        position: 'center'
    }).then(r => { if (r.isConfirmed) document.getElementById('formSelesai').submit(); });
}
</script>
@endpush
@endsection
