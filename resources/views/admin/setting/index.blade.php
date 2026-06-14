@extends('layouts.app')

@section('page-title', 'Pengaturan Sistem')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card-table">
            <div class="card-header-title mb-3">
                <i class="bi bi-gear"></i> PIN Khusus Pemilik Kos (Master PIN)
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('admin.setting.update_master_pin') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Status Master PIN:</label>
                    <div>
                        @if($masterPin && $masterPin->value)
                            <span class="badge bg-success"><i class="bi bi-shield-lock-fill"></i> Telah Diatur & Aktif</span>
                        @else
                            <span class="badge bg-danger"><i class="bi bi-shield-exclamation"></i> Belum Diatur</span>
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Set/Reset Master PIN Baru (6 Digit)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-asterisk"></i></span>
                        <input type="text" name="master_pin" class="form-control" placeholder="Biarkan kosong jika tidak ingin mengubah PIN" pattern="[0-9]{6}" maxlength="6">
                    </div>
                    <small class="text-muted">Masukkan 6 digit angka baru.</small>
                    @error('master_pin')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Kamar</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-door-open"></i></span>
                        <select name="kamar_ids[]" class="form-select">
                            <option value="">-- Pilih Kamar --</option>
                            @foreach($kamars as $kamar)
                                <option value="{{ $kamar->id }}" {{ in_array($kamar->id, $allowedRooms) ? 'selected' : '' }}>
                                    Kamar {{ $kamar->nomor_kamar }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Pengaturan
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
