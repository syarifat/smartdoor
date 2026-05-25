<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PenghuniController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\KamarController as AdminKamarController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Admin\PenghuniController as AdminPenghuniController;
use App\Http\Controllers\Admin\TagihanController as AdminTagihanController;
use App\Http\Controllers\Penghuni\TagihanController as PenghuniTagihanController;
use App\Http\Controllers\Admin\LaporanGangguanController as AdminLaporanGangguanController;
use App\Http\Controllers\Penghuni\LaporanGangguanController as PenghuniLaporanGangguanController;

Route::get('/', function () {
    return view('welcome');
});

// Redirect setelah login berdasarkan role
Route::get('/dashboard', function () {
    if (Auth::user()?->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('penghuni.dashboard');
})->middleware(['auth'])->name('dashboard');

// ===== ADMIN ROUTES =====
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::resource('kamar', AdminKamarController::class);
    Route::resource('kartu', \App\Http\Controllers\Admin\KartuController::class);
    Route::get('/penghuni/search', [AdminPenghuniController::class, 'search'])->name('penghuni.search');
    Route::resource('penghuni', AdminPenghuniController::class);
    Route::delete('/pendaftar/{id}', [AdminController::class, 'hapusPendaftar'])->name('pendaftar.destroy');
    Route::post('/penghuni/{id}/set-pin', [AdminPenghuniController::class, 'setPinPenghuni'])->name('penghuni.set_pin');
    Route::patch('/penghuni/{id}/toggle-pin', [AdminPenghuniController::class, 'togglePinAktif'])->name('penghuni.toggle_pin');
    Route::get('/log', [AdminController::class, 'log'])->name('log.index');
    Route::get('/percobaan-gagal', [AdminController::class, 'percobaanGagal'])->name('percobaan.index');
    Route::delete('/percobaan-gagal/{id}', [AdminController::class, 'hapusPercobaan'])->name('percobaan.destroy');
    // Laporan Kehilangan
    Route::get('/laporan-kehilangan', [AdminController::class, 'laporanKehilangan'])->name('laporan.index');
    Route::patch('/laporan-kehilangan/{id}/proses', [AdminController::class, 'prosesLaporan'])->name('laporan.proses');
    Route::patch('/laporan-kehilangan/{id}/selesai', [AdminController::class, 'selesaikanLaporan'])->name('laporan.selesai');
    
    // Laporan Gangguan
    Route::resource('laporan-gangguan', AdminLaporanGangguanController::class)->except(['create', 'store', 'edit', 'update']);
    Route::patch('laporan-gangguan/{laporan_gangguan}/proses', [AdminLaporanGangguanController::class, 'proses'])->name('laporan-gangguan.proses');
    Route::patch('laporan-gangguan/{laporan_gangguan}/selesai', [AdminLaporanGangguanController::class, 'selesai'])->name('laporan-gangguan.selesai');

    // JSON endpoint untuk real-time dashboard
    Route::get('/api/aktivitas-terbaru', [AdminController::class, 'aktivitasTerbaru'])->name('api.aktivitas');
    
    // Tagihan
    Route::get('/tagihan', [AdminTagihanController::class, 'index'])->name('tagihan.index');
    Route::get('/tagihan/buat', [AdminTagihanController::class, 'buat'])->name('tagihan.buat');
    Route::post('/tagihan/buat', [AdminTagihanController::class, 'simpan'])->name('tagihan.simpan');
    Route::patch('/tagihan/{id}/verifikasi', [AdminTagihanController::class, 'verifikasi'])->name('tagihan.verifikasi');
    Route::delete('/tagihan/{id}', [AdminTagihanController::class, 'hapus'])->name('tagihan.hapus');
    
    // Update Profile/Nomor WA Admin
    Route::post('/update-nomor-hp', [AdminController::class, 'updateNomorHp'])->name('update_nomor_hp');
});

// ===== PENGHUNI ROUTES =====
Route::middleware(['auth', 'penghuni'])->prefix('penghuni')->name('penghuni.')->group(function () {
    Route::get('/dashboard', [PenghuniController::class, 'dashboard'])->name('dashboard');
    Route::get('/akses', [PenghuniController::class, 'akses'])->name('akses');
    
    // Laporan Gangguan
    Route::get('/laporan-gangguan', [PenghuniLaporanGangguanController::class, 'index'])->name('laporan-gangguan.index');
    Route::post('/laporan-gangguan', [PenghuniLaporanGangguanController::class, 'store'])->name('laporan-gangguan.store');
    Route::get('/laporan-gangguan/tracking', [PenghuniLaporanGangguanController::class, 'tracking'])->name('laporan-gangguan.tracking');

    Route::get('/laporan-kehilangan', [PenghuniController::class, 'laporKehilangan'])->name('laporan.index');
    Route::post('/laporan-kehilangan', [PenghuniController::class, 'storeLaporan'])->name('laporan.store');
    
    // Tagihan Penghuni
    Route::get('/tagihan', [PenghuniTagihanController::class, 'index'])->name('tagihan.index');
    Route::post('/akses/buka-pintu', [PenghuniController::class, 'bukaPintuWeb'])->name('akses.buka_pintu');
    Route::get('/tagihan/{id}/bayar', [PenghuniTagihanController::class, 'showBayar'])->name('tagihan.show_bayar');
    Route::post('/tagihan/{id}/bayar', [PenghuniTagihanController::class, 'bayar'])->name('tagihan.bayar');
    Route::post('/tagihan/{id}/upload-bukti', [PenghuniTagihanController::class, 'uploadBukti'])->name('tagihan.upload_bukti');
    Route::post('/tagihan/{id}/check-status', [PenghuniTagihanController::class, 'checkStatus'])->name('tagihan.check_status');

    // Update Profile/Nomor WA Penghuni
    Route::post('/update-nomor-hp', [PenghuniController::class, 'updateNomorHp'])->name('update_nomor_hp');
});

// Midtrans Webhook Notification
Route::post('/penghuni/tagihan/notification', [PenghuniTagihanController::class, 'notification'])->name('penghuni.tagihan.notification');

// ===== PROFILE =====
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ====== Register & Verifikasi Email =====
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::get('/verifikasi-email/{token}', [RegisteredUserController::class, 'verifikasiEmail'])->name('verifikasi.email');
Route::get('/cek-email', function() {
    return view('auth.cek_email');
})->name('cek.email');

// ====== IoT API Endpoints ======
// Endpoint untuk alat ESP32/NodeMCU (Abaikan CSRF di bootstrap/app.php jika post dari hardware)
Route::post('/api/iot/door-status', [\App\Http\Controllers\Api\IotController::class, 'updateDoorStatus']);
Route::post('/api/iot/access', [\App\Http\Controllers\Api\IotController::class, 'recordAccess']);
Route::post('/api/iot/akses-pin', [\App\Http\Controllers\Api\IotController::class, 'aksesPin']);
Route::post('/api/iot/percobaan-gagal', [\App\Http\Controllers\Api\IotController::class, 'percobaanGagal']);
// Polling & konfirmasi perintah pintu (GET tidak perlu auth untuk ESP32)
Route::get('/api/iot/status-pintu/{kamar_id}', [\App\Http\Controllers\Api\IotController::class, 'statusPintu']);
Route::post('/api/iot/konfirmasi-perintah/{kamar_id}', [\App\Http\Controllers\Api\IotController::class, 'konfirmasiPerintah']);

// ===== CUSTOM LOGOUT =====
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login')->with('success', 'Anda berhasil logout!');
})->middleware('auth')->name('logout');

// ============================================================
// CRON ENDPOINT — dipanggil oleh cron job pihak ketiga
// URL: GET /cron/queue?key=YOUR_CRON_SECRET_KEY
// Fungsi: memproses antrian email (kirim verifikasi registrasi)
// ============================================================
Route::get('/cron/queue', function () {
    $key = request('key');
    $expected = env('CRON_SECRET_KEY');

    // Validasi secret key agar tidak sembarangan bisa diakses
    if (!$expected || $key !== $expected) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Proses semua job di antrian lalu berhenti otomatis
    Artisan::call('queue:work', [
        '--stop-when-empty' => true,
        '--timeout'         => 55,  // Batas 55 detik agar tidak timeout hosting
        '--tries'           => 3,   // Coba ulang 3x jika gagal
        '--queue'           => 'default',
    ]);

    return response()->json([
        'ok'     => true,
        'output' => Artisan::output(),
        'time'   => now()->toDateTimeString(),
    ]);
})->name('cron.queue');

require __DIR__.'/auth.php';