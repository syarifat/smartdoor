@extends('layouts.app')

@section('page-title', 'Tambah Kartu RFID Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card-table">
            <h5 class="card-header-title mb-4"><i class="bi bi-plus-circle"></i> Form Kartu Baru</h5>

            <form action="{{ route('admin.kartu.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">UID Kartu <span class="text-danger">*</span></label>
                    <input type="text" name="uid" class="form-control @error('uid') is-invalid @enderror" value="{{ old('uid') }}" placeholder="Contoh: A1B2C3D4" required>
                    <div class="form-text">Tempelkan kartu ke reader untuk mengetahui UID atau ketik manual.</div>
                    @error('uid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Penghuni (Opsional)</label>
                    <select name="penghuni_id" class="form-select @error('penghuni_id') is-invalid @enderror">
                        <option value="">-- Jangan tugaskan dulu --</option>
                        @foreach($penghunis as $p)
                            <option value="{{ $p->id }}" {{ old('penghuni_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->nama }} {{ $p->kamar ? '(Kamar: '.$p->kamar->nomor_kamar.')' : '(Belum ada kamar)' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Pilih penghuni jika kartu ini langsung diberikan kepada mereka.</div>
                    @error('penghuni_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <hr class="mb-4 text-muted">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.kartu.index') }}" class="btn btn-light px-4 rounded-pill" style="border:1px solid #ddd;">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill" style="background:#2d6a9f; border:none;">Simpan Kartu</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
