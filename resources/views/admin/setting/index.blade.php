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
                    <label class="form-label fw-semibold">Master PIN Saat Ini:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shield-lock-fill"></i></span>
                        <input type="password" id="current_master_pin" class="form-control bg-light" value="{{ $masterPin }}" readonly placeholder="Belum Diatur">
                        <button class="btn btn-outline-secondary" type="button" id="toggle_pin_btn" onclick="togglePinVisibility()">
                            <i class="bi bi-eye" id="toggle_pin_icon"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Set/Reset Master PIN Baru</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-card-heading"></i></span>
                        <input type="text" name="master_pin" class="form-control" placeholder="Biarkan kosong jika tidak ingin mengubah PIN" value="{{ old('master_pin') }}">
                    </div>
                    <small class="text-muted">UID Kartu RFID milik owner/admin yang dapat membuka seluruh pintu kamar kos.</small>
                    @error('master_pin')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Pengaturan
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function togglePinVisibility() {
    const pinInput = document.getElementById('current_master_pin');
    const pinIcon = document.getElementById('toggle_pin_icon');
    if (pinInput.type === 'password') {
        pinInput.type = 'text';
        pinIcon.classList.remove('bi-eye');
        pinIcon.classList.add('bi-eye-slash');
    } else {
        pinInput.type = 'password';
        pinIcon.classList.remove('bi-eye-slash');
        pinIcon.classList.add('bi-eye');
    }
}
</script>
@endsection
