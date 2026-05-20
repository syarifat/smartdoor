@extends('layouts.app')

@section('page-title', 'Edit Kamar')

@section('content')

<div class="card-table" style="max-width: 500px;">
    <div class="card-header-title mb-3">
        <i class="bi bi-pencil-square"></i> Edit Kamar {{ $kamar->nomor_kamar }}
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('admin.kamar.update', $kamar) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label fw-semibold">Nomor Kamar</label>
            <input type="text" name="nomor_kamar" class="form-control"
                   value="{{ old('nomor_kamar', $kamar->nomor_kamar) }}" required>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold">Status Kamar</label>
            <select name="status" class="form-select" required>
                <option value="tersedia" {{ $kamar->status == 'tersedia' ? 'selected' : '' }}>
                    Tersedia
                </option>
                <option value="terisi" {{ $kamar->status == 'terisi' ? 'selected' : '' }}>
                    Terisi
                </option>
            </select>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-warning">
                <i class="bi bi-save"></i> Update
            </button>
            <a href="{{ route('admin.kamar.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Batal
            </a>
        </div>
    </form>
</div>

@endsection