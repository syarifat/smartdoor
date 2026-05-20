@extends('layouts.app')

@section('page-title', 'Edit Penghuni')

@section('content')

<div class="card-table" style="max-width: 600px;">
    <div class="card-header-title mb-3">
        <i class="bi bi-pencil-square"></i> Edit Data Penghuni
    </div>

    @if($penghuni->user)
        <div class="alert alert-light border d-flex align-items-center gap-3 mb-4" style="border-radius:10px;">
            <div style="width:44px;height:44px;background:linear-gradient(135deg,#1e3a5f,#2d6a9f);border-radius:50%;
                        display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px;flex-shrink:0;">
                <i class="bi bi-person-check"></i>
            </div>
            <div>
                <div style="font-size:13px;font-weight:700;color:#1e3a5f;">{{ $penghuni->user->name }}</div>
                <div style="font-size:12px;color:#888;">
                    <i class="bi bi-envelope me-1"></i>{{ $penghuni->user->email }}
                    &nbsp;·&nbsp;
                    <span class="badge bg-success" style="font-size:10px;">Punya Akun</span>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div><i class="bi bi-exclamation-circle me-1"></i>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('admin.penghuni.update', $penghuni) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Nama --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Nama Lengkap</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" name="nama" class="form-control"
                       value="{{ old('nama', $penghuni->nama) }}" required>
            </div>
        </div>

        {{-- Telepon --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Nomor Telepon</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                <input type="text" name="telepon" class="form-control"
                       value="{{ old('telepon', $penghuni->telepon) }}">
            </div>
        </div>

        {{-- Kamar --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Kamar</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-door-open"></i></span>
                <select name="kamar_id" class="form-select">
                    <option value="">-- Pilih Kamar --</option>
                    @foreach($kamars as $kamar)
                        <option value="{{ $kamar->id }}"
                            {{ $penghuni->kamar_id == $kamar->id ? 'selected' : '' }}>
                            Kamar {{ $kamar->nomor_kamar }}
                            @if($kamar->status === 'terisi' && $penghuni->kamar_id != $kamar->id)
                                (Terisi)
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Alamat --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Alamat Asal</label>
            <div class="input-group">
                <span class="input-group-text" style="align-items:flex-start;padding-top:10px;">
                    <i class="bi bi-geo-alt"></i>
                </span>
                <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $penghuni->alamat) }}</textarea>
            </div>
        </div>

        {{-- Foto KTP --}}
        <div class="mb-4">
            <label class="form-label fw-semibold">Foto KTP</label>

            @if($penghuni->foto_ktp)
                <div class="mb-2">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-info text-dark"><i class="bi bi-image me-1"></i>Foto KTP saat ini</span>
                        <small class="text-muted">Upload baru untuk mengganti</small>
                    </div>
                    <img src="{{ asset('storage/' . $penghuni->foto_ktp) }}"
                         alt="Foto KTP"
                         style="max-height:150px;border-radius:8px;border:1px solid #ddd;object-fit:cover;display:block;cursor:pointer;"
                         onclick="showKtpModal(this.src)" title="Klik untuk perbesar">
                </div>
            @else
                <div class="mb-2">
                    <span class="badge bg-secondary"><i class="bi bi-exclamation-circle me-1"></i>Belum ada foto KTP</span>
                </div>
            @endif

            <input type="file" name="foto_ktp" class="form-control"
                   accept="image/jpeg,image/png" onchange="previewKtpEdit(this)">
            <small class="text-muted">jpg/png, maks 2MB. Kosongkan jika tidak ingin mengubah.</small>
            <img id="ktp-edit-preview" src="" alt="Preview"
                 style="display:none;margin-top:8px;max-height:150px;border-radius:8px;border:1px solid #ddd;object-fit:cover;cursor:pointer;"
                 onclick="showKtpModal(this.src)" title="Klik untuk perbesar">
        </div>

        @if($penghuni->anggotaKeluargas && $penghuni->anggotaKeluargas->count() > 0)
        <div class="mb-4">
            <label class="form-label fw-semibold">Data Anggota Kamar (Keluarga/Rekanan)</label>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Anggota</th>
                            <th>Hubungan</th>
                            <th>Telepon</th>
                            <th class="text-center">Foto KTP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($penghuni->anggotaKeluargas as $anggota)
                        <tr>
                            <td>{{ $anggota->nama }}</td>
                            <td><span class="badge bg-secondary">{{ $anggota->hubungan }}</span></td>
                            <td>{{ $anggota->telepon ?? '-' }}</td>
                            <td class="text-center">
                                @if($anggota->foto_ktp)
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            onclick="showKtpModal('{{ asset('storage/' . $anggota->foto_ktp) }}')">
                                        <i class="bi bi-eye"></i> Lihat KTP
                                    </button>
                                @else
                                    <span class="text-muted small">Tidak ada</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-warning">
                <i class="bi bi-save"></i> Update Profil
            </button>
            <a href="{{ route('admin.penghuni.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </form>
</div>

<div class="card-table mt-4" style="max-width: 600px;">
    <div class="card-header-title mb-3 text-primary">
        <i class="bi bi-key"></i> Pengaturan PIN Akses (Keypad)
    </div>
    
    <div class="alert alert-info small">
        <strong>Informasi:</strong> Penghuni dapat menggunakan kartu RFID maupun PIN (jika diaktifkan) untuk membuka pintu kamar.
    </div>

    <div class="mb-4 d-flex align-items-center justify-content-between p-3 border rounded bg-light">
        <div>
            <div class="fw-bold mb-1">Status PIN Saat Ini</div>
            @if($penghuni->pin_aktif)
                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> PIN Aktif</span>
            @else
                <span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i> PIN Nonaktif</span>
            @endif
            @if(!$penghuni->pin)
                <div class="text-danger small mt-1"><i class="bi bi-exclamation-triangle"></i> PIN belum diatur!</div>
            @endif
        </div>
        <form action="{{ route('admin.penghuni.toggle_pin', $penghuni->id) }}" method="POST">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-sm {{ $penghuni->pin_aktif ? 'btn-outline-danger' : 'btn-outline-success' }}" {{ !$penghuni->pin ? 'disabled' : '' }}>
                {{ $penghuni->pin_aktif ? 'Nonaktifkan PIN' : 'Aktifkan PIN' }}
            </button>
        </form>
    </div>

    <form action="{{ route('admin.penghuni.set_pin', $penghuni->id) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Set/Reset PIN Baru (6 Digit)</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-asterisk"></i></span>
                <input type="text" name="pin" class="form-control" placeholder="123456" pattern="[0-9]{6}" maxlength="6" required>
                <button class="btn btn-primary" type="submit">Simpan PIN</button>
            </div>
            <small class="text-muted">Masukkan 6 digit angka.</small>
        </div>
    </form>
</div>

<!-- Modal Preview KTP -->
<div class="modal fade" id="ktpModalEdit" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-card-heading"></i> Preview KTP</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center p-4">
        <img id="ktpImagePreviewEdit" src="" alt="KTP Error" class="img-fluid rounded shadow" style="max-height: 700px; width: 100%; object-fit: contain;">
      </div>
    </div>
  </div>
</div>

<style>
/* Efek background blur khusus untuk Modal Preview KTP */
.modal-backdrop.show {
    backdrop-filter: blur(8px);
    background-color: rgba(0, 0, 0, 0.6);
}
</style>

<script>
@if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        timer: 3000,
        showConfirmButton: false,
        timerProgressBar: true
    });
@endif

function previewKtpEdit(input) {
    const preview = document.getElementById('ktp-edit-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function showKtpModal(src) {
    document.getElementById('ktpImagePreviewEdit').src = src;
    var ktpModal = new bootstrap.Modal(document.getElementById('ktpModalEdit'));
    ktpModal.show();
}
</script>

@endsection