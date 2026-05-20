@extends('layouts.app')

@section('page-title', 'Edit Kartu RFID')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card-table">
            <h5 class="card-header-title mb-4"><i class="bi bi-pencil-square"></i> Form Edit Kartu</h5>

            <form action="{{ route('admin.kartu.update', $kartu->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-semibold">UID Kartu <span class="text-danger">*</span></label>
                    <input type="text" name="uid" class="form-control @error('uid') is-invalid @enderror" value="{{ old('uid', $kartu->uid) }}" required>
                    @error('uid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Penghuni (Opsional)</label>
                    <select name="penghuni_id" class="form-select @error('penghuni_id') is-invalid @enderror">
                        <option value="">-- Jangan tugaskan dulu --</option>
                        @foreach($penghunis as $p)
                            <option value="{{ $p->id }}" {{ old('penghuni_id', $kartu->penghuni_id) == $p->id ? 'selected' : '' }}>
                                {{ $p->nama }} {{ $p->kamar ? '(Kamar: '.$p->kamar->nomor_kamar.')' : '(Belum ada kamar)' }}
                            </option>
                        @endforeach
                    </select>
                    @error('penghuni_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="aktif" {{ old('status', $kartu->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ old('status', $kartu->status) == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        <option value="hilang" {{ old('status', $kartu->status) == 'hilang' ? 'selected' : '' }}>Hilang</option>
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <hr class="mb-4 text-muted">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.kartu.index') }}" class="btn btn-light px-4 rounded-pill" style="border:1px solid #ddd;">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill" style="background:#2d6a9f; border:none;">Update Kartu</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
