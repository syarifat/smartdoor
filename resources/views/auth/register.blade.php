<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar Akun - Smart Door</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 40px 20px;
    position: relative;
    overflow-x: hidden;
}

/* ── BACKGROUND FOTO (sama persis dengan login) ── */
.bg-image {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background-image: url('/images/kos.png');
    background-size: cover;
    background-position: center;
    z-index: 0;
    filter: brightness(0.5);
    animation: zoomBG 20s ease infinite alternate;
}

@keyframes zoomBG {
    from { transform: scale(1); }
    to   { transform: scale(1.08); }
}

/* ── OVERLAY GELAP (sama persis dengan login) ── */
.bg-overlay {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: linear-gradient(135deg,
        rgba(15, 32, 39, 0.75) 0%,
        rgba(32, 58, 67, 0.65) 50%,
        rgba(44, 83, 100, 0.75) 100%);
    z-index: 1;
}

/* ── CONTAINER ── */
.container {
    width: 500px;
    background: rgba(255, 255, 255, 0.97);
    backdrop-filter: blur(20px);
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.5);
    position: relative;
    z-index: 2;
    animation: fadeIn 1s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(30px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── LOGO AREA ── */
.logo-area {
    text-align: center;
    margin-bottom: 24px;
}
.logo-area .icon {
    width: 56px; height: 56px;
    background: linear-gradient(135deg, #1e3a5f, #2c5364);
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    color: #fff;
    margin-bottom: 10px;
    animation: pulse 2s ease infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50%       { transform: scale(1.08); }
}

.logo-area h2 {
    font-size: 20px;
    font-weight: 700;
    color: #1e3a5f;
    margin-bottom: 2px;
}
.logo-area p {
    font-size: 12px;
    color: #888;
}

/* ── FORM GROUP ── */
.form-group {
    margin-bottom: 14px;
}
.form-group label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #444;
    margin-bottom: 5px;
}
.form-group .input-wrap {
    position: relative;
}
.form-group .input-wrap i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #aaa;
    font-size: 15px;
}
.form-group .input-wrap.textarea-wrap i {
    top: 14px;
    transform: none;
}

input, textarea {
    width: 100%;
    padding: 11px 12px 11px 36px;
    border-radius: 8px;
    border: 1.5px solid #e0e0e0;
    font-size: 13px;
    transition: 0.3s;
    outline: none;
    font-family: 'Poppins', sans-serif;
    background: #f9f9f9;
}
textarea {
    resize: vertical;
    min-height: 70px;
}
input:focus, textarea:focus {
    border-color: #2c5364;
    background: white;
    box-shadow: 0 0 0 3px rgba(44, 83, 100, 0.1);
}

select {
    width: 100%;
    padding: 11px 12px 11px 36px;
    border-radius: 8px;
    border: 1.5px solid #e0e0e0;
    font-size: 13px;
    outline: none;
    font-family: 'Poppins', sans-serif;
    background: #f9f9f9;
    transition: 0.3s;
    color: #555;
}
select:focus {
    border-color: #2c5364;
    background: white;
    box-shadow: 0 0 0 3px rgba(44, 83, 100, 0.1);
}

/* ── FILE UPLOAD ── */
.file-upload-area {
    border: 1.5px dashed #b0c8d8;
    border-radius: 8px;
    padding: 16px;
    text-align: center;
    background: #f4f8fb;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
}
.file-upload-area:hover {
    border-color: #2c5364;
    background: #eef5fb;
}
.file-upload-area input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
}
.file-upload-area i {
    font-size: 28px;
    color: #2c5364;
    display: block;
    margin-bottom: 6px;
}
.file-upload-area span {
    font-size: 12px;
    color: #666;
}
.file-upload-area .file-name {
    font-size: 12px;
    color: #2c5364;
    font-weight: 600;
    margin-top: 4px;
}
#ktp-preview {
    display: none;
    margin-top: 10px;
    width: 100%;
    max-height: 150px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #ddd;
}

/* ── DIVIDER ── */
.divider {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 16px 0;
}
.divider hr {
    flex: 1;
    border: none;
    border-top: 1px solid #eee;
}
.divider span {
    font-size: 11px;
    color: #aaa;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* ── ANGGOTA BLOCK ── */
.anggota-block {
    background: #f4f8fb;
    padding: 15px;
    border-radius: 10px;
    border: 1px solid #d0e3f0;
    margin-bottom: 15px;
    margin-top: 15px;
}
.anggota-block p {
    font-size: 13px;
    font-weight: 700;
    color: #2c5364;
    border-bottom: 1px solid #d0e3f0;
    padding-bottom: 6px;
    margin-bottom: 10px;
}

/* ── BUTTON ── */
button[type="submit"] {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 8px;
    background: linear-gradient(135deg, #1e3a5f, #2c5364);
    color: white;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    letter-spacing: 0.5px;
    margin-top: 6px;
    transition: 0.3s;
}
button[type="submit"]:hover {
    background: linear-gradient(135deg, #2c5364, #1e3a5f);
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(44, 83, 100, 0.4);
}

/* ── FOOTER ── */
.footer {
    margin-top: 16px;
    text-align: center;
    font-size: 13px;
    color: #666;
}
.footer a {
    color: #2c5364;
    text-decoration: none;
    font-weight: 600;
}
.footer a:hover {
    text-decoration: underline;
}

/* ── ERROR ── */
.error {
    color: #dc3545;
    font-size: 11px;
    margin-top: 4px;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* ── COPYRIGHT ── */
.copy {
    margin-top: 10px;
    font-size: 11px;
    text-align: center;
    color: #bbb;
}
</style>
</head>
<body>

<!-- BACKGROUND (sama dengan login) -->
<div class="bg-image"></div>
<div class="bg-overlay"></div>

<div class="container">

    <div class="logo-area">
        <div class="icon"><i class="bi bi-shield-lock"></i></div>
        <h2>Buat Akun Penghuni</h2>
        <p>Kos Bu Rini – Smart Door System</p>
    </div>

    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
    @csrf

    {{-- Nama --}}
    <div class="form-group">
        <label>Nama Lengkap</label>
        <div class="input-wrap">
            <i class="bi bi-person"></i>
            <input type="text" name="name" placeholder="Nama lengkap Anda"
                   value="{{ old('name') }}" required>
        </div>
        @error('name') <div class="error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div> @enderror
    </div>

    {{-- Email --}}
    <div class="form-group">
        <label>Alamat Email</label>
        <div class="input-wrap">
            <i class="bi bi-envelope"></i>
            <input type="email" name="email" placeholder="email@domain.com"
                   value="{{ old('email') }}" required>
        </div>
        @error('email') <div class="error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div> @enderror
    </div>

    {{-- Nomor HP --}}
    <div class="form-group">
        <label>Nomor HP / WhatsApp</label>
        <div class="input-wrap">
            <i class="bi bi-phone"></i>
            <input type="text" name="telepon" placeholder="08xxxxxxxxxx"
                   value="{{ old('telepon') }}" required>
        </div>
        @error('telepon') <div class="error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div> @enderror
    </div>

    {{-- Alamat --}}
    <div class="form-group">
        <label>Alamat Asal</label>
        <div class="input-wrap textarea-wrap">
            <i class="bi bi-geo-alt"></i>
            <textarea name="alamat" placeholder="Alamat lengkap asal Anda" required>{{ old('alamat') }}</textarea>
        </div>
        @error('alamat') <div class="error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div> @enderror
    </div>

    {{-- Foto KTP --}}
    <div class="form-group">
        <label>Foto KTP <span style="color:#888;font-weight:400">(jpg/png, maks 2MB)</span></label>
        <div class="file-upload-area" id="ktp-drop">
            <input type="file" name="foto_ktp" id="foto_ktp_input"
                   accept="image/jpeg,image/png" onchange="previewKtp(this)">
            <i class="bi bi-card-image"></i>
            <span>Klik atau drag foto KTP di sini</span>
            <div class="file-name" id="ktp-filename">Belum ada file dipilih</div>
        </div>
        <img id="ktp-preview" src="" alt="Preview KTP">
        @error('foto_ktp') <div class="error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div> @enderror
    </div>

    <div class="divider">
        <hr><span>informasi kamar</span><hr>
    </div>

    {{-- Total Orang --}}
    <div class="form-group">
        <label>Total Orang di Kamar (Satu Keluarga)</label>
        <div class="input-wrap">
            <i class="bi bi-people"></i>
            <select name="total_orang" id="total_orang">
                <option value="1" selected>1 Orang (Hanya saya)</option>
                <option value="2">2 Orang</option>
                <option value="3">3 Orang</option>
                <option value="4">4 Orang</option>
                <option value="5">5 Orang</option>
            </select>
        </div>
    </div>

    <div id="anggota-container"></div>

    <div class="divider">
        <hr><span>keamanan akun</span><hr>
    </div>

    {{-- Password --}}
    <div class="form-group">
        <label>Password</label>
        <div class="input-wrap">
            <i class="bi bi-lock"></i>
            <input type="password" id="password" name="password" placeholder="Minimal 8 karakter" required>
            <i class="bi bi-eye-slash toggle-pw" id="togglePassword" style="left:auto; right:12px; cursor:pointer;" onclick="togglePasswordVisibility('password', 'togglePassword')"></i>
        </div>
        @error('password') <div class="error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div> @enderror
    </div>

    {{-- Konfirmasi Password --}}
    <div class="form-group">
        <label>Konfirmasi Password</label>
        <div class="input-wrap">
            <i class="bi bi-lock-fill"></i>
            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password" required>
            <i class="bi bi-eye-slash toggle-pw" id="togglePasswordConfirm" style="left:auto; right:12px; cursor:pointer;" onclick="togglePasswordVisibility('password_confirmation', 'togglePasswordConfirm')"></i>
        </div>
    </div>

    <button type="submit">
        <i class="bi bi-person-check"></i> Daftar Sekarang →
    </button>

    </form>

    <div class="footer">
        Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a>
    </div>

    <div class="copy">© 2025 Smart Door Monitoring System</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    }
}

function previewKtp(input) {
    const preview = document.getElementById('ktp-preview');
    const filename = document.getElementById('ktp-filename');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
        filename.textContent = input.files[0].name;
    }
}

document.getElementById('total_orang').addEventListener('change', function() {
    const container = document.getElementById('anggota-container');
    container.innerHTML = '';
    let total = parseInt(this.value);
    for (let i = 0; i < total - 1; i++) {
        let html = `
        <div class="anggota-block">
            <p><i class="bi bi-person-badge"></i> Anggota Kamar ke-${i+1}</p>
            <div class="form-group" style="margin-bottom:10px;">
                <label>Nama Lengkap</label>
                <div class="input-wrap">
                    <i class="bi bi-person"></i>
                    <input type="text" name="anggota[${i}][nama]" placeholder="Nama Anggota" required>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:10px;">
                <label>Hubungan</label>
                <div class="input-wrap">
                    <i class="bi bi-diagram-3"></i>
                    <input type="text" name="anggota[${i}][hubungan]" placeholder="Contoh: Istri, Suami, Anak" required>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:10px;">
                <label>Nomor HP (Bila ada)</label>
                <div class="input-wrap">
                    <i class="bi bi-phone"></i>
                    <input type="text" name="anggota[${i}][telepon]" placeholder="08xxx">
                </div>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label>Foto KTP Anggota</label>
                <input type="file" name="anggota[${i}][foto_ktp]" accept="image/jpeg,image/png"
                       style="padding:6px; font-size:12px; padding-left:10px;">
            </div>
        </div>`;
        container.insertAdjacentHTML('beforeend', html);
    }
});

document.querySelector('form').addEventListener('submit', async function(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Memproses...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });
    let formData = new FormData(this);
    try {
        let response = await fetch("{{ route('register') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        let result = await response.json();
        if (response.ok && result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Akun Berhasil Dibuat!',
                text: 'Mengarahkan ke Dashboard...',
                showConfirmButton: false,
                timer: 2000
            }).then(() => {
                window.location.href = "{{ route('dashboard') }}";
            });
        } else {
            if (response.status === 422) {
                let errors = result.errors;
                let errorMessages = [];
                for (let field in errors) {
                    errorMessages.push(errors[field][0]);
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: errorMessages.join('<br>'),
                    confirmButtonColor: '#2c5364'
                });
            } else {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            }
        }
    } catch(e) {
        Swal.fire('Error', 'Koneksi gagal', 'error');
    }
});
</script>
</body>
</html>