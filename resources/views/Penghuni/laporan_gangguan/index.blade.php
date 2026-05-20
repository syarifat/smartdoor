@extends('layouts.app')
@section('page-title', 'Laporan Gangguan')
@section('content')
<style>
.page-header{display:flex;align-items:center;gap:12px;margin-bottom:24px;}
.header-icon{width:46px;height:46px;background:linear-gradient(135deg,#fff3cd,#ffc107);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#856404;font-size:22px;}
.page-header h5{font-weight:700;color:#1e3a5f;margin:0;}
.form-card{background:#fff;border-radius:14px;padding:28px;box-shadow:0 2px 12px rgba(0,0,0,0.07);border-left:4px solid #ffc107;margin-bottom:24px;}
.riwayat-card{background:#fff;border-radius:14px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,0.06);}
.urgensi-option{border:2px solid #e9ecef;border-radius:10px;padding:14px;cursor:pointer;transition:all .2s;display:block;}
.urgensi-option:hover{border-color:#2d6a9f;background:#f0f6ff;}
.urgensi-option input{display:none;}
.urgensi-option.selected-normal{border-color:#6c757d;background:#f8f9fa;}
.urgensi-option.selected-mendesak{border-color:#dc3545;background:#fff5f5;}
.urgensi-icon{font-size:24px;margin-bottom:6px;}
.no-lp{background:#f1f5f9;padding:3px 8px;border-radius:6px;font-family:monospace;font-size:12px;color:#1e3a5f;font-weight:700;}
.badge-baru{background:#fde8e8;color:#dc3545;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.badge-diproses{background:#fff3cd;color:#856404;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.badge-selesai{background:#d1f0da;color:#157347;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.tracking-link-card{background:linear-gradient(135deg,#1e3a5f,#2d6a9f);border-radius:12px;padding:16px 20px;color:#fff;margin-bottom:24px;display:flex;align-items:center;gap:12px;}
</style>

<div class="page-header">
    <div class="header-icon"><i class="bi bi-tools"></i></div>
    <div>
        <h5>Laporan Gangguan</h5>
        <div class="text-muted" style="font-size:13px;">Laporkan masalah di kamar Anda, kami segera menangani</div>
    </div>
</div>

@if(!$penghuni || !$noKamar || $noKamar === '-')
<div class="alert alert-warning" style="border-radius:10px;">
    <i class="bi bi-info-circle me-2"></i>
    Anda belum memiliki kamar aktif. Hubungi admin untuk informasi lebih lanjut.
</div>
@else



{{-- Banner Kontak Admin --}}
@if(isset($adminNomor) && $adminNomor)
<div class="alert alert-info d-flex align-items-center mb-4" style="border-radius:10px; background-color: #e0f2fe; border: 1px solid #bae6fd; color: #0284c7;">
    <i class="bi bi-info-circle-fill me-3" style="font-size: 24px;"></i>
    <div>
        <strong>Pemberitahuan:</strong><br>
        Jika pemilik belum merespon tindakan laporan Anda, silahkan hubungi nomor WhatsApp berikut: 
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $adminNomor) }}" target="_blank" class="fw-bold" style="color: #0369a1; text-decoration: underline;">
            https://wa.me/{{ preg_replace('/[^0-9]/', '', $adminNomor) }} <i class="bi bi-whatsapp"></i>
        </a>
    </div>
</div>
@endif

{{-- Form Laporan Baru --}}
<div class="form-card">
    <h6 class="fw-bold mb-4" style="color:#1e3a5f;"><i class="bi bi-plus-circle me-2 text-warning"></i>Buat Laporan Baru</h6>

    <form action="{{ route('penghuni.laporan-gangguan.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-3">
            {{-- Kategori --}}
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Kategori Gangguan <span class="text-danger">*</span></label>
                <select name="kategori" id="kategori" class="form-select @error('kategori') is-invalid @enderror" required>
                    <option value="">— Pilih Kategori —</option>
                    @foreach(['Listrik'=>'bi-lightning-charge','Air'=>'bi-droplet','Furnitur'=>'bi-box','Pintu & Kunci'=>'bi-door-open','Internet'=>'bi-wifi','Lainnya'=>'bi-three-dots'] as $kat => $icon)
                    <option value="{{ $kat }}" {{ old('kategori')===$kat?'selected':'' }}>{{ $kat }}</option>
                    @endforeach
                </select>
                @error('kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Deskripsi --}}
            <div class="col-12">
                <label class="form-label fw-semibold">Deskripsi Masalah <span class="text-danger">*</span></label>
                <textarea name="deskripsi" rows="4" class="form-control @error('deskripsi') is-invalid @enderror"
                    placeholder="Jelaskan secara detail masalah yang Anda alami (kapan, dimana, apa yang terjadi)..." required>{{ old('deskripsi') }}</textarea>
                @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>



            {{-- Foto Bukti --}}
            <div class="col-12">
                <label class="form-label fw-semibold">Foto Bukti <span class="text-muted fw-normal">(opsional, maks. 3MB)</span></label>
                <input type="file" name="foto_bukti" accept="image/*" class="form-control @error('foto_bukti') is-invalid @enderror" id="foto_bukti">
                @error('foto_bukti')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div id="preview-foto" class="mt-2" style="display:none;">
                    <img id="preview-img" src="" alt="Preview" style="max-height:180px;border-radius:8px;border:2px solid #e9ecef;">
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-warning fw-bold px-4" style="color:#fff;">
                <i class="bi bi-send me-2"></i>Kirim Laporan
            </button>
            <button type="reset" class="btn btn-outline-secondary">Reset</button>
        </div>
    </form>
</div>

{{-- Riwayat Laporan --}}
<div class="riwayat-card">
    <h6 class="fw-bold mb-3" style="color:#1e3a5f;"><i class="bi bi-clock-history me-2"></i>Riwayat Laporan Saya</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="font-size:12px;">No. Laporan</th>
                    <th style="font-size:12px;">Kategori</th>
                    <th style="font-size:12px;">Status</th>
                    <th style="font-size:12px;">Tanggal</th>
                    <th style="font-size:12px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($riwayat as $lap)
                <tr>
                    <td><code class="no-lp">{{ $lap->no_laporan }}</code></td>
                    <td style="font-size:13px;">
                        @php $katIcon = match($lap->kategori) { 'Listrik'=>'bi-lightning-charge','Air'=>'bi-droplet','Furnitur'=>'bi-box','Pintu & Kunci'=>'bi-door-open','Internet'=>'bi-wifi',default=>'bi-three-dots' }; @endphp
                        <i class="bi {{ $katIcon }} me-1 text-muted"></i>{{ $lap->kategori }}
                    </td>
                    <td>
                        @if($lap->status==='baru')
                            <span class="badge-baru"><i class="bi bi-circle-fill me-1" style="font-size:7px;"></i>Baru</span>
                        @elseif($lap->status==='diproses')
                            <span class="badge-diproses"><i class="bi bi-arrow-repeat me-1"></i>Diproses</span>
                        @else
                            <span class="badge-selesai"><i class="bi bi-check-circle me-1"></i>Selesai</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-size:12px;">{{ $lap->created_at->format('d/m/Y') }}</div>
                        <div class="text-muted" style="font-size:11px;">{{ $lap->created_at->format('H:i') }}</div>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('penghuni.laporan-gangguan.tracking') }}?no_laporan={{ $lap->no_laporan }}"
                           class="btn btn-sm btn-outline-primary" style="font-size:11px;padding:3px 10px;border-radius:8px;">
                            <i class="bi bi-info-circle me-1"></i>Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size:36px;display:block;margin-bottom:8px;"></i>
                        Belum ada laporan yang dibuat.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endif

@push('scripts')
<script>
document.getElementById('foto_bukti')?.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('preview-foto').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
@endsection
