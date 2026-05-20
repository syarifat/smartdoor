<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Smart Door Kos Bu Rini</title>
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
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    overflow: hidden;
}

/* BACKGROUND FOTO */
.bg-image {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background-image: url('/images/kos.png');
    background-size: cover;
    background-position: center;
    z-index: 0;
    animation: zoomBG 20s ease infinite alternate;
}

@keyframes zoomBG {
    from { transform: scale(1); }
    to   { transform: scale(1.08); }
}

/* CONTAINER */
.container {
    display: flex;
    width: 850px;
    height: 460px;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.5);
    animation: fadeIn 1s ease;
    position: relative;
    z-index: 2;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(30px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* LEFT - Glass Effect */
.left {
    flex: 1;
    background: linear-gradient(135deg,
        rgba(15, 32, 39, 0.9),
        rgba(44, 83, 100, 0.85));
    backdrop-filter: blur(10px);
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    padding: 40px;
    animation: slideLeft 1s ease;
    border-right: 1px solid rgba(255,255,255,0.1);
}

@keyframes slideLeft {
    from { transform: translateX(-50px); opacity: 0; }
    to   { transform: translateX(0); opacity: 1; }
}

.left .icon-lock {
    margin-bottom: 20px;
    animation: pulse 2.5s ease infinite;
}

.left .icon-lock i {
    font-size: 65px;
    background: -webkit-linear-gradient(45deg, #00d2ff, #3a7bd5);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 0 15px rgba(0, 210, 255, 0.6));
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50%       { transform: scale(1.1); }
}

.left h1 {
    font-size: 28px;
    font-weight: 600;
    margin-bottom: 8px;
    letter-spacing: 2px;
}

.left h3 {
    font-weight: 400;
    opacity: 0.85;
    font-size: 16px;
}

.left p {
    font-size: 12px;
    opacity: 0.6;
    margin-top: 10px;
    line-height: 1.6;
}

.left .divider {
    width: 40px;
    height: 2px;
    background: rgba(255,255,255,0.4);
    margin: 15px auto;
    border-radius: 2px;
}

/* RIGHT - Glass Effect */
.right {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 40px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    animation: slideRight 1s ease;
}

@keyframes slideRight {
    from { transform: translateX(50px); opacity: 0; }
    to   { transform: translateX(0); opacity: 1; }
}

.right h2 {
    margin-bottom: 8px;
    color: #1e3a5f;
    font-size: 22px;
    font-weight: 600;
}

.right .subtitle {
    color: #888;
    font-size: 12px;
    margin-bottom: 25px;
}

/* INPUT GROUP */
.input-group {
    position: relative;
    margin-bottom: 20px;
}

.input-group input {
    width: 100%;
    padding: 12px 12px 12px 40px;
    border: 1.5px solid #e0e0e0;
    border-radius: 8px;
    outline: none;
    font-size: 13px;
    transition: 0.3s;
    background: #f9f9f9;
}

.input-group input:focus {
    border-color: #2c5364;
    background: white;
    box-shadow: 0 0 0 3px rgba(44,83,100,0.1);
}

.input-group .input-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #aaa;
    font-size: 15px;
}

.input-group label {
    position: absolute;
    left: 40px;
    top: 12px;
    color: #999;
    font-size: 13px;
    transition: 0.3s;
    background: transparent;
    padding: 0 5px;
    pointer-events: none;
}

.input-group input:focus + label,
.input-group input:valid + label {
    top: -8px;
    font-size: 10px;
    color: #2c5364;
    background: white;
    left: 35px;
}

/* BUTTON */
button[type="submit"] {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 8px;
    background: linear-gradient(135deg, #1e3a5f, #2c5364);
    color: white;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: 0.3s;
    letter-spacing: 0.5px;
}

button[type="submit"]:hover {
    background: linear-gradient(135deg, #2c5364, #1e3a5f);
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(44,83,100,0.4);
}

/* ALERT SUCCESS */
.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 12px;
    text-align: center;
}

/* LINK */
.link {
    text-align: center;
    margin-top: 15px;
}

.link a {
    font-size: 12px;
    color: #2c5364;
    text-decoration: none;
    font-weight: 500;
}

.link a:hover {
    text-decoration: underline;
}

/* FOOTER */
.footer {
    margin-top: 12px;
    font-size: 11px;
    text-align: center;
    color: #bbb;
}

/* ERROR */
.error {
    color: #dc3545;
    font-size: 11px;
    margin-top: -15px;
    margin-bottom: 10px;
}

/* RESPONSIVE MOBILE */
@media (max-width: 768px) {
    .container {
        flex-direction: column;
        width: 90%;
        height: auto;
        min-height: 500px;
    }
    
    .left {
        flex: none;
        padding: 30px 20px;
        border-right: none;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .left h1 { font-size: 24px; }
    .left h3 { font-size: 14px; }
    
    .right {
        flex: none;
        padding: 30px 20px;
    }
}
</style>
</head>
<body>

<!-- BACKGROUND FOTO -->
<div class="bg-image"></div>

<div class="container">

    <!-- LEFT -->
    <div class="left">
        <div class="icon-lock"><i class="bi bi-shield-lock-fill"></i></div>
        <h1>SMART DOOR</h1>
        <div class="divider"></div>
        <h3>Kos Bu Rini</h3>
        <p>Sistem Manajemen Kos Berbasis<br>Akses Pintu Cerdas</p>
    </div>

    <!-- RIGHT -->
    <div class="right">

        <h2>Selamat Datang</h2>
        <p class="subtitle">Masuk ke Sistem Manajemen Kos Bu Rini</p>

        @if(session('success'))
        <div class="alert-success">
            ✅ {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="input-group">
            <span class="input-icon">✉️</span>
            <input type="email" name="email" required value="{{ old('email') }}">
            <label>Email</label>
        </div>
        @error('email') <div class="error">{{ $message }}</div> @enderror

        <div class="input-group">
            <span class="input-icon">🔑</span>
            <input type="password" name="password" id="password" required>
            <label>Password</label>
            <span onclick="togglePassword()"
                  style="position:absolute; right:12px; top:50%;
                         transform:translateY(-50%); cursor:pointer; color:#aaa;">
                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg"
                     width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                    <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
                </svg>
            </span>
        </div>
        @error('password') <div class="error">{{ $message }}</div> @enderror

        <button type="submit">Masuk →</button>

        </form>

        <div class="link">
            <a href="{{ route('register') }}">Belum punya akun? Daftar sekarang</a>
        </div>

        <div class="footer">
            © 2025 Smart Door - Kos Bu Rini
        </div>

    </div>

</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.innerHTML = `<path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755l-.809-.805zm-4.706-4.706l-1.096-1.096c.216-.16.48-.256.766-.256 1.38 0 2.5 1.12 2.5 2.5 0 .287-.096.551-.256.767l-1.096-1.096A1.5 1.5 0 0 0 8.653 6.533zM1.173 8a13.133 13.133 0 0 1 1.66-2.043l1.246 1.246A11.59 11.59 0 0 0 2.213 8c.058.087.122.183.195.288.335.48.83 1.12 1.465 1.755C5.121 11.332 6.88 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l1.236 1.236A7.054 7.054 0 0 1 8 13.5C3 13.5 0 8 0 8s.074-.136.216-.388l.957-.99-.001-.001zm3.896-1.897L2.457 3.49 3.165 2.78l10.05 10.05-.708.708-2.612-2.612A2.497 2.497 0 0 1 8 10.5c-1.38 0-2.5-1.12-2.5-2.5a2.5 2.5 0 0 1 .569-1.596z"/>`;
    } else {
        passwordInput.type = 'password';
        eyeIcon.innerHTML = `<path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/><path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>`;
    }
}
</script>
</body>
</html>