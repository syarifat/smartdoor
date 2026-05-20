<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cek Email - Smart Door Kos Bu Rini</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
    filter: brightness(0.5);
    animation: zoomBG 20s ease infinite alternate;
}

@keyframes zoomBG {
    from { transform: scale(1); }
    to   { transform: scale(1.08); }
}

/* OVERLAY GELAP */
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

/* CONTAINER */
.container {
    width: 450px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.5);
    text-align: center;
    position: relative;
    z-index: 2;
    animation: fadeIn 1s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(30px); }
    to   { opacity: 1; transform: translateY(0); }
}

.icon-wrapper {
    font-size: 60px;
    color: #2c5364;
    margin-bottom: 20px;
    animation: pulse 2s ease infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

h2 {
    font-size: 22px;
    font-weight: 700;
    color: #1e3a5f;
    margin-bottom: 15px;
}

p {
    font-size: 14px;
    color: #555;
    line-height: 1.6;
    margin-bottom: 10px;
}

p.small-text {
    font-size: 12px;
    color: #888;
    margin-bottom: 30px;
}

.btn-back {
    display: inline-block;
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    background: linear-gradient(135deg, #1e3a5f, #2c5364);
    color: white;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: 0.3s;
    letter-spacing: 0.5px;
}

.btn-back:hover {
    background: linear-gradient(135deg, #2c5364, #1e3a5f);
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(44, 83, 100, 0.4);
}

.footer {
    margin-top: 25px;
    font-size: 11px;
    color: #aaa;
}

.alert {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 13px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
</style>
</head>
<body>

<div class="bg-image"></div>
<div class="bg-overlay"></div>

<div class="container">
    <div class="icon-wrapper">
        <i class="bi bi-envelope-paper-heart"></i>
    </div>
    
    <h2>Cek Email Kamu</h2>

    @if(session('error') || session('warning'))
        <div class="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') ?? session('warning') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif

    <p>Kami telah mengirim link verifikasi ke alamat email yang Anda daftarkan.</p>
    <p class="small-text">Silakan periksa kotak masuk (Inbox) atau folder Spam/Junk jika tidak ada.</p>
    
    <a href="{{ route('login') }}" class="btn-back">
        <i class="bi bi-arrow-left"></i> Kembali ke Login
    </a>
    
    <div class="footer">
        © 2025 Smart Door Monitoring System
    </div>
</div>

</body>
</html>
