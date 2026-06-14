@extends('layouts.app')

@section('page-title', 'Tambah Penghuni')

@section('content')

<div class="card-table" style="max-width: 600px;">
    <div class="card-header-title mb-3">
        <i class="bi bi-person-plus"></i> Tambah Penghuni Baru
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div><i class="bi bi-exclamation-circle me-1"></i>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if(!$prefilledUser)
    {{-- Info box autocomplete --}}
    <div class="alert alert-info d-flex align-items-start gap-2 mb-4" style="font-size:13px;">
        <i class="bi bi-lightbulb-fill mt-1" style="color:#0d6efd;flex-shrink:0"></i>
        <div>
            <strong>Tips:</strong> Ketik nama penghuni pada kolom di bawah untuk mencari penghuni yang sudah mendaftar.
            Pilih nama dari daftar, dan data akan <strong>terisi otomatis</strong>.
        </div>
    </div>
    @endif

    <form action="{{ route('admin.penghuni.store') }}" method="POST" enctype="multipart/form-data" id="form-penghuni">
        @csrf

        @if($prefilledUser)
            {{-- ============================================================ --}}
            {{-- MODE 1: PENETAPAN KAMAR OTOMATIS (Ada prefilledUser)         --}}
            {{-- ============================================================ --}}
            <input type="hidden" name="user_id" value="{{ $prefilledUser->id }}">
            <input type="hidden" name="nama" value="{{ $prefilledUser->name }}">
            <input type="hidden" name="telepon" value="{{ $prefilledUser->telepon }}">
            <input type="hidden" name="alamat" value="{{ $prefilledUser->alamat }}">

            {{-- Kartu Ringkasan Informasi Pendaftar --}}
            <div class="card shadow-sm border-0 mb-4" style="border-radius:12px; background:#f8fafc; border-left:4px solid #2d6a9f !important;">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-primary mb-3"><i class="bi bi-card-checklist me-2"></i>Informasi Pendaftar</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Nama Lengkap</small>
                            <span class="fw-semibold text-dark fs-6">{{ $prefilledUser->name }}</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Email Akun</small>
                            <span class="fw-semibold text-dark">{{ $prefilledUser->email }}</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Nomor Telepon</small>
                            <span class="fw-semibold text-dark">{{ $prefilledUser->telepon ?? '-' }}</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Alamat Asal</small>
                            <span class="fw-semibold text-dark">{{ $prefilledUser->alamat ?? '-' }}</span>
                        </div>
                    </div>

                    @if($prefilledUser->foto_ktp)
                        <div class="mt-3 pt-3 border-top border-light">
                            <small class="text-muted d-block mb-1">Foto KTP</small>
                            <img src="{{ asset('storage/' . str_replace('\\', '/', $prefilledUser->foto_ktp)) }}" alt="Foto KTP"
                                 style="max-height:120px; border-radius:8px; border:1px solid #ddd; object-fit:cover; cursor:pointer;"
                                 onclick="showKtpModal(this.src)" title="Klik untuk perbesar">
                        </div>
                    @endif
                </div>
            </div>

            {{-- Data Anggota Keluarga jika ada --}}
            @if($prefilledUser->anggotaKeluargas && $prefilledUser->anggotaKeluargas->count() > 0)
                <div class="card shadow-sm border-0 mb-4" style="border-radius:12px; background:#f8fafc;">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-people-fill me-2"></i>Data Anggota Kamar (Keluarga/Rekanan)</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Anggota</th>
                                        <th>Hubungan</th>
                                        <th>Telepon</th>
                                        <th class="text-center">Foto KTP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prefilledUser->anggotaKeluargas as $a)
                                        <tr>
                                            <td class="fw-semibold text-dark">{{ $a->nama }}</td>
                                            <td><span class="badge bg-secondary">{{ $a->hubungan }}</span></td>
                                            <td>{{ $a->telepon ?? '-' }}</td>
                                            <td class="text-center">
                                                @if($a->foto_ktp)
                                                    <button type="button" class="btn btn-sm btn-outline-info px-2 py-0"
                                                            onclick="showKtpModal('{{ asset('storage/' . str_replace('\\', '/', $a->foto_ktp)) }}')">
                                                        <i class="bi bi-eye"></i> Lihat
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
                </div>
            @endif

            {{-- Form Kamar (Hanya ini yang ditampilkan untuk diisi) --}}
            <div class="card shadow-sm border-0 mb-4" style="border-radius:12px; border:1.5px solid #2d6a9f;">
                <div class="card-body p-4">
                    <label class="form-label fw-bold text-dark fs-5 mb-2">Penetapan Kamar Kos</label>
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white"><i class="bi bi-door-open-fill"></i></span>
                        <select name="kamar_id" class="form-select form-select-lg fw-semibold" required>
                            <option value="">-- Pilih Kamar Tersedia --</option>
                            @foreach($kamars as $kamar)
                                <option value="{{ $kamar->id }}"
                                    {{ old('kamar_id') == $kamar->id ? 'selected' : '' }}>
                                    Kamar {{ $kamar->nomor_kamar }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <small class="text-muted mt-2 d-block"><i class="bi bi-info-circle"></i> Pilih kamar yang tersedia untuk menempatkan penghuni ini.</small>
                </div>
            </div>

            {{-- UID Kartu Cadangan --}}
            <div class="mb-4">
                <label class="form-label fw-semibold">UID Kartu e-KTP / Cadangan (Opsional)</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-credit-card-2-front"></i></span>
                    <input type="text" name="uid_kartu_cadangan" class="form-control"
                           value="{{ old('uid_kartu_cadangan') }}" placeholder="Contoh: A1B2C3D4">
                </div>
                <small class="text-muted">Gunakan kartu ini jika kartu utama bermasalah. Tempel e-KTP di reader untuk mendapatkan UID.</small>
            </div>

        @else
            {{-- ============================================================ --}}
            {{-- MODE 2: TAMBAH MANUAL & PENCARIAN (Buka manual kosong)       --}}
            {{-- ============================================================ --}}
            <input type="hidden" name="user_id" id="user_id">

            {{-- Nama --}}
            <div class="mb-3 position-relative">
                <label class="form-label fw-semibold">Nama Lengkap</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="nama" id="input-nama" class="form-control"
                           placeholder="Ketik nama penghuni..." value="{{ old('nama') }}" required
                           autocomplete="off">
                    <span class="input-group-text" id="badge-terpilih" style="display:none;background:#d1fae5;border-color:#86efac;">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <small class="ms-1 text-success fw-semibold">Dari Registrasi</small>
                    </span>
                </div>

                {{-- Dropdown Autocomplete --}}
                <div id="autocomplete-dropdown" style="
                    display:none;
                    position:absolute;
                    top:100%; left:0; right:0; z-index:999;
                    background:#fff;
                    border:1.5px solid #2d6a9f;
                    border-radius:0 0 10px 10px;
                    box-shadow:0 8px 24px rgba(0,0,0,0.12);
                    max-height:260px;
                    overflow-y:auto;
                ">
                    <div id="autocomplete-list"></div>
                    <div id="autocomplete-empty" style="display:none;padding:16px;text-align:center;color:#aaa;font-size:13px;">
                        <i class="bi bi-search"></i> Tidak ada penghuni terdaftar dengan nama tersebut
                    </div>
                </div>
            </div>

            {{-- Email (readonly, terisi otomatis) --}}
            <div class="mb-3" id="row-email" style="display:none;">
                <label class="form-label fw-semibold">Email Akun</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="text" id="display-email" class="form-control" readonly
                           style="background:#f8f9fa;color:#555;">
                </div>
            </div>

            {{-- Nomor Telepon --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Nomor Telepon</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-phone"></i></span>
                    <input type="text" name="telepon" id="input-telepon" class="form-control"
                           placeholder="08xxxxxxxxxx" value="{{ old('telepon') }}">
                </div>
            </div>

            {{-- Alamat --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Alamat Asal</label>
                <div class="input-group">
                    <span class="input-group-text" style="align-items:flex-start;padding-top:10px;">
                        <i class="bi bi-geo-alt"></i>
                    </span>
                    <textarea name="alamat" id="input-alamat" class="form-control" rows="2"
                              placeholder="Alamat asal penghuni">{{ old('alamat') }}</textarea>
                </div>
            </div>

            {{-- Kamar --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Kamar <span class="text-muted fw-normal">(wajib dipilih)</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-door-open"></i></span>
                    <select name="kamar_id" class="form-select" required>
                        <option value="">-- Pilih Kamar --</option>
                        @foreach($kamars as $kamar)
                            <option value="{{ $kamar->id }}"
                                {{ old('kamar_id') == $kamar->id ? 'selected' : '' }}>
                                Kamar {{ $kamar->nomor_kamar }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- UID Kartu Cadangan --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">UID Kartu e-KTP / Cadangan (Opsional)</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-credit-card-2-front"></i></span>
                    <input type="text" name="uid_kartu_cadangan" class="form-control"
                           value="{{ old('uid_kartu_cadangan') }}" placeholder="Contoh: A1B2C3D4">
                </div>
                <small class="text-muted">Gunakan kartu ini jika kartu utama bermasalah. Tempel e-KTP di reader untuk mendapatkan UID.</small>
            </div>

            {{-- Foto KTP --}}
            <div class="mb-4">
                <label class="form-label fw-semibold">Foto KTP</label>

                {{-- Preview KTP dari registrasi --}}
                <div id="ktp-from-register" style="display:none;margin-bottom:10px;">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>KTP sudah ada dari registrasi</span>
                        <small class="text-muted">Upload baru untuk mengganti</small>
                    </div>
                    <img id="ktp-preview-register" src="" alt="Foto KTP"
                         style="max-height:150px;border-radius:8px;border:1px solid #ddd;object-fit:cover;cursor:pointer;"
                         onclick="showKtpModal(this.src)" title="Klik untuk perbesar">
                </div>

                <input type="file" name="foto_ktp" id="foto_ktp_input" class="form-control"
                       accept="image/jpeg,image/png" onchange="previewKtpBaru(this)">
                <small class="text-muted">jpg/png, maks 2MB. Kosongkan jika tidak ingin mengubah foto.</small>
                <img id="ktp-preview-baru" src="" alt="Preview"
                     style="display:none;margin-top:8px;max-height:150px;border-radius:8px;border:1px solid #ddd;object-fit:cover;cursor:pointer;"
                     onclick="showKtpModal(this.src)" title="Klik untuk perbesar">
            </div>

            {{-- Container untuk anggota kamar --}}
            <div id="anggota-container-register" style="display:none;" class="mb-4">
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
                        <tbody id="anggota-list-register">
                        </tbody>
                    </table>
                </div>
                <small class="text-muted"><i class="bi bi-info-circle"></i> Anggota ini didapat dari data registrasi penghuni.</small>
            </div>
        @endif

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                <i class="bi bi-save"></i> Simpan Penghuni
            </button>
            <a href="{{ route('admin.penghuni.index') }}" class="btn btn-secondary px-3">
                <i class="bi bi-arrow-left"></i> Batal
            </a>
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
.autocomplete-item {
    padding: 10px 16px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: background 0.15s;
}
.autocomplete-item:hover {
    background: #eef5ff;
}
.autocomplete-item:last-child { border-bottom: none; }
.autocomplete-avatar {
    width: 36px; height: 36px;
    background: linear-gradient(135deg, #1e3a5f, #2d6a9f);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 15px; flex-shrink: 0;
}
.autocomplete-name { font-weight: 600; font-size: 13px; color: #1e3a5f; }
.autocomplete-sub  { font-size: 11px; color: #888; }

.modal-backdrop.show {
    backdrop-filter: blur(8px);
    background-color: rgba(0, 0, 0, 0.6);
}
</style>

<script>
// Fungsi modal preview KTP (digunakan di semua mode)
function showKtpModal(src) {
    document.getElementById('ktpImagePreviewEdit').src = src;
    var ktpModal = new bootstrap.Modal(document.getElementById('ktpModalEdit'));
    ktpModal.show();
}

@if(!$prefilledUser)
// ============================================================
// LOGIKA JAVASCRIPT HANYA UNTUK MODE MANUAL / AUTOCOMPLETE
// ============================================================
const searchUrl = "{{ route('admin.penghuni.search') }}";
let debounceTimer;

const inputNama     = document.getElementById('input-nama');
const inputTelepon  = document.getElementById('input-telepon');
const inputAlamat   = document.getElementById('input-alamat');
const fieldPenghuniId = document.getElementById('user_id');
const dropdown      = document.getElementById('autocomplete-dropdown');
const list          = document.getElementById('autocomplete-list');
const empty         = document.getElementById('autocomplete-empty');
const badgeTerpilih = document.getElementById('badge-terpilih');
const rowEmail      = document.getElementById('row-email');
const displayEmail  = document.getElementById('display-email');
const ktpFromReg    = document.getElementById('ktp-from-register');
const ktpPreviewReg = document.getElementById('ktp-preview-register');

inputNama.addEventListener('input', function () {
    const q = this.value.trim();
    clearTimeout(debounceTimer);

    if (fieldPenghuniId.value) resetPilihan();

    if (q.length < 1) { closeDropdown(); return; }

    debounceTimer = setTimeout(() => fetchPenghuni(q), 250);
});

inputNama.addEventListener('blur', function () {
    setTimeout(closeDropdown, 200);
});

function fetchPenghuni(q) {
    fetch(`${searchUrl}?q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(data => renderDropdown(data));
}

function renderDropdown(data) {
    list.innerHTML = '';
    if (data.length === 0) {
        empty.style.display = 'block';
        list.style.display  = 'none';
    } else {
        empty.style.display = 'none';
        list.style.display  = 'block';
        data.forEach(p => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item';
            item.innerHTML = `
                <div class="autocomplete-avatar"><i class="bi bi-person"></i></div>
                <div>
                    <div class="autocomplete-name">${p.nama}</div>
                    <div class="autocomplete-sub">
                        <i class="bi bi-envelope me-1"></i>${p.email}
                        ${p.telepon ? `&nbsp;·&nbsp;<i class="bi bi-phone me-1"></i>${p.telepon}` : ''}
                    </div>
                </div>
            `;
            item.addEventListener('mousedown', () => pilihPenghuni(p));
            list.appendChild(item);
        });
    }
    dropdown.style.display = 'block';
}

function pilihPenghuni(p) {
    inputNama.value       = p.nama;
    inputTelepon.value    = p.telepon;
    inputAlamat.value     = p.alamat;
    fieldPenghuniId.value = p.id;
    displayEmail.value    = p.email;

    badgeTerpilih.style.display = 'flex';
    rowEmail.style.display      = 'block';

    inputTelepon.readOnly = true;
    inputAlamat.readOnly  = true;

    if (p.foto_ktp) {
        ktpPreviewReg.src       = p.foto_ktp;
        ktpFromReg.style.display = 'block';
    } else {
        ktpFromReg.style.display = 'none';
    }

    const anggotaContainer = document.getElementById('anggota-container-register');
    const anggotaList = document.getElementById('anggota-list-register');
    if (p.anggota && p.anggota.length > 0) {
        anggotaList.innerHTML = '';
        p.anggota.forEach(a => {
            const tr = document.createElement('tr');
            let fotoHtml = '<span class="text-muted small">Tidak ada</span>';
            if (a.foto_ktp) {
                fotoHtml = `<button type="button" class="btn btn-sm btn-outline-info" onclick="showKtpModal('${a.foto_ktp}')"><i class="bi bi-eye"></i> Lihat KTP</button>`;
            }
            tr.innerHTML = `
                <td>${a.nama}</td>
                <td><span class="badge bg-secondary">${a.hubungan}</span></td>
                <td>${a.telepon || '-'}</td>
                <td class="text-center">${fotoHtml}</td>
            `;
            anggotaList.appendChild(tr);
        });
        anggotaContainer.style.display = 'block';
    } else {
        anggotaContainer.style.display = 'none';
    }

    closeDropdown();
}

function resetPilihan() {
    fieldPenghuniId.value       = '';
    badgeTerpilih.style.display = 'none';
    rowEmail.style.display      = 'none';
    inputTelepon.readOnly       = false;
    inputAlamat.readOnly        = false;
    ktpFromReg.style.display    = 'none';
    document.getElementById('anggota-container-register').style.display = 'none';
}

function closeDropdown() {
    dropdown.style.display = 'none';
}

function previewKtpBaru(input) {
    const preview = document.getElementById('ktp-preview-baru');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('#input-nama') && !e.target.closest('#autocomplete-dropdown')) {
        closeDropdown();
    }
});
@endif
</script>

@endsection