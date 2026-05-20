@extends('layouts.app')

@section('page-title', 'Tambah Kamar')

@section('content')

<div class="card-table" style="max-width: 500px;">
    <div class="card-header-title mb-3">
        <i class="bi bi-plus-circle"></i> Tambah Kamar Baru
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('admin.kamar.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-semibold">Nomor Kamar</label>
            <input type="text" name="nomor_kamar" class="form-control"
                   placeholder="Contoh: A1, B2, 101"
                   value="{{ old('nomor_kamar') }}" required>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold">Status Kamar</label>
            <select name="status" class="form-select" required>
                <option value="tersedia" {{ old('status') == 'tersedia' ? 'selected' : '' }}>
                    Tersedia
                </option>
                <option value="terisi" {{ old('status') == 'terisi' ? 'selected' : '' }}>
                    Terisi
                </option>
            </select>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Simpan
            </button>
            <a href="{{ route('admin.kamar.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Batal
            </a>
        </div>
    </form>
</div>

@endsection