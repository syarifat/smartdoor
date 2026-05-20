@extends('layouts.app')

@section('page-title', 'Buat Tagihan Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card-table">
            <h5 class="card-header-title mb-4"><i class="bi bi-plus-circle"></i> Form Buat Tagihan</h5>

            <form action="{{ route('admin.tagihan.simpan') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Penghuni & Kamar</label>
                    <select name="penghuni_id" class="form-select @error('penghuni_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Penghuni --</option>
                        @foreach($penghunis as $p)
                            <option value="{{ $p->id }}" {{ old('penghuni_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->nama }} - Kamar {{ $p->kamar->nomor_kamar ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                    @error('penghuni_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Bulan Tagihan</label>
                    <input type="text" name="bulan" class="form-control @error('bulan') is-invalid @enderror" 
                           placeholder="Contoh: Mei 2026" value="{{ old('bulan') }}" required>
                    @error('bulan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Jumlah Tagihan (Rp)</label>
                    <input type="number" name="jumlah_tagihan" class="form-control @error('jumlah_tagihan') is-invalid @enderror" 
                           placeholder="Contoh: 500000" value="{{ old('jumlah_tagihan') }}" required min="0">
                    @error('jumlah_tagihan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tanggal Dibuat</label>
                    <input type="date" name="tanggal_tagihan" class="form-control @error('tanggal_tagihan') is-invalid @enderror" 
                           value="{{ old('tanggal_tagihan', date('Y-m-d')) }}" required>
                    @error('tanggal_tagihan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Keterangan (Opsional)</label>
                    <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="2">{{ old('keterangan') }}</textarea>
                    @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('admin.tagihan.index') }}" class="btn btn-light px-4 rounded-pill border">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill" style="background:#2d6a9f; border:none;">Buat Tagihan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
