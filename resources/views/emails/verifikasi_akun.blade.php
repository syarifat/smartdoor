<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body { font-family: 'Arial', sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
.wrapper { max-width: 600px; margin: 40px auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
.header { background: linear-gradient(135deg, #1e3a5f, #2c5364); padding: 40px; text-align: center; color: white; }
.header h1 { margin: 0; font-size: 24px; letter-spacing: 2px; }
.header p { margin: 5px 0 0; opacity: 0.8; font-size: 13px; }
.body { padding: 40px; color: #333; }
.body h2 { color: #1e3a5f; margin-bottom: 15px; }
.body p { line-height: 1.7; color: #555; font-size: 14px; }
.btn-wrapper { text-align: center; margin: 30px 0; }
.btn { display: inline-block; padding: 14px 40px; background: linear-gradient(135deg, #1e3a5f, #2c5364); color: white !important; text-decoration: none; border-radius: 8px; font-size: 15px; font-weight: bold; letter-spacing: 1px; }
.warning { background: #fff8e1; border-left: 4px solid #ffc107; padding: 12px 15px; border-radius: 4px; font-size: 13px; color: #856404; margin-top: 20px; }
.footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #888; border-top: 1px solid #eee; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>🔐 SMART DOOR</h1>
    <p>Kos Bu Rini — Sistem Keamanan Akses Pintu</p>
  </div>
  <div class="body">
    <h2>Halo, {{ $nama }}!</h2>
    <p>Terima kasih telah mendaftar di <strong>Smart Door Kos Bu Rini</strong>.</p>
    <p>Untuk mengaktifkan akun Anda, silakan klik tombol di bawah ini:</p>
    <div class="btn-wrapper">
      <a href="{{ $verificationUrl }}" class="btn">✅ VERIFIKASI AKUN</a>
    </div>
    <div class="warning">
      ⏱️ <strong>Link berlaku selama 60 menit.</strong><br>
      Jika Anda tidak merasa mendaftar, abaikan email ini.
    </div>
  </div>
  <div class="footer">
    © 2025 Smart Door Kos Bu Rini — Sistem Manajemen Akses Pintu Cerdas
  </div>
</div>
</body>
</html>
