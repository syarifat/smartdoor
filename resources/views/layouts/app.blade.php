<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kos Bu Rini – Smart Door</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        body { background: #f4f7f6; margin: 0; font-family: 'Inter', sans-serif; }
        #sidebar {
            width: 260px; height: 100vh;
            background: linear-gradient(180deg, #1e3a5f 0%, #2d6a9f 100%);
            position: fixed; top: 0; left: 0; z-index: 1040;
            display: flex; flex-direction: column;
            transition: transform 0.3s ease;
        }
        #sidebar nav {
            flex-grow: 1; overflow-y: auto; overflow-x: hidden;
            -ms-overflow-style: none; scrollbar-width: none;
            padding-bottom: 20px;
        }
        #sidebar nav::-webkit-scrollbar { display: none; }
        #sidebar .brand {
            padding: 30px 20px 25px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        #sidebar .brand h5 { color: #fff; margin: 0; font-weight: 700; font-size: 18px; display:flex; align-items:center; justify-content:center; gap:8px;}
        #sidebar .brand small { color: rgba(255,255,255,0.7); font-size: 12px; font-weight: 600; display: block; margin-top: 5px;}
        
        #sidebar .nav-link {
            color: rgba(255,255,255,0.8); padding: 12px 20px;
            border-radius: 10px; margin: 4px 16px;
            display: flex; align-items: center; gap: 12px;
            font-size: 14px; font-weight: 500;
            transition: all 0.2s ease; text-decoration: none;
        }
        #sidebar .nav-link:hover {
            color: #1e3a5f; background: #fff;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        #sidebar .nav-link.active {
            background: rgba(255,255,255,0.2); color: #fff; font-weight: 600;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        #sidebar .nav-link i { font-size: 18px; width: 22px; text-align: center; }
        #sidebar .nav-section {
            color: rgba(255,255,255,0.5); font-size: 11px;
            font-weight: 600; letter-spacing: 0.5px;
            padding: 20px 20px 5px; text-transform: uppercase;
        }
        #sidebar .logout-area {
            padding: 15px; border-top: 1px solid rgba(255,255,255,0.05);
            background: rgba(0,0,0,0.05);
        }
        
        /* Modal Konfirmasi Logout */
        #logoutModal .modal-content {
            border-radius: 16px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.18);
        }
        #logoutModal .modal-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d6a9f 100%); border-radius: 16px 16px 0 0; border-bottom: none; padding: 20px 24px;
        }
        #logoutModal .modal-header .modal-title { color: #fff; font-weight: 600; font-size: 16px; }
        #logoutModal .modal-header .btn-close { filter: invert(1) brightness(2); }
        #logoutModal .modal-body { padding: 28px 24px 10px; text-align: center; }
        #logoutModal .logout-icon {
            width: 70px; height: 70px; background: #fff5f5; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px; font-size: 32px; color: #dc3545;
        }
        #logoutModal .modal-body h6 { font-weight: 600; color: #1e3a5f; font-size: 16px; margin-bottom: 6px; }
        #logoutModal .modal-body p { color: #888; font-size: 13px; margin-bottom: 0; }
        #logoutModal .modal-footer {
            border-top: 1px solid #f0f0f0; padding: 16px 24px 20px; display: flex; gap: 10px; justify-content: center;
        }
        #logoutModal .btn-cancel {
            padding: 9px 26px; border-radius: 8px; font-weight: 600; font-size: 14px;
            border: 2px solid #dee2e6; background: #fff; color: #555; transition: all 0.2s;
        }
        #logoutModal .btn-cancel:hover { background: #f8f9fa; border-color: #adb5bd; }
        #logoutModal .btn-logout-confirm {
            padding: 9px 26px; border-radius: 8px; font-weight: 600; font-size: 14px;
            border: none; background: #dc3545; color: #fff; transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(220,53,69,0.2);
        }
        #logoutModal .btn-logout-confirm:hover { background: #b02a37; transform: translateY(-1px); }

        #main-content { margin-left: 260px; min-height: 100vh; transition: margin 0.3s ease; }
        
        .topbar {
            background: #fff; padding: 18px 35px;
            border-bottom: 1px solid #f0f0f0;
            display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 1030;
            /* box-shadow: 0 4px 20px rgba(0,0,0,0.01); */
        }
        
        .topbar-left { display: flex; align-items: center; gap: 15px; }
        .topbar-left .accent-line { width: 5px; height: 28px; background: #2d6a9f; border-radius: 4px; }
        .topbar-left .page-title { font-weight: 700; color: #1e3a5f; margin: 0; font-size: 18px; line-height: 1.2;}
        .topbar-left .page-subtitle { font-size: 11px; color: #888; font-weight: 500;}
        
        .topbar-right { display: flex; align-items: center; gap: 15px; }
        .topbar-right .user-info { text-align: right; }
        .topbar-right .user-info small { display: block; font-size: 11px; color: #888; text-transform: capitalize; }
        .topbar-right .user-info strong { display: block; font-size: 13px; color: #1e3a5f; font-weight: 600; }
        
        .topbar .avatar {
            width: 42px; height: 42px; background: #e8f0f8;
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; color: #2d6a9f; font-size: 18px;
            position: relative;
        }


        .content-area { padding: 35px; }
        
        /* Stat Cards */
        .stat-card {
            background: #fff; border-radius: 20px; padding: 24px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.03); 
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,0.06); }
        .stat-card.blue .stat-icon { background: #3b82f6; color: #fff; box-shadow: 0 6px 16px rgba(59,130,246,0.3); }
        .stat-card.green .stat-icon { background: #10b981; color: #fff; box-shadow: 0 6px 16px rgba(16,185,129,0.3); }
        .stat-card.orange .stat-icon { background: #f59e0b; color: #fff; box-shadow: 0 6px 16px rgba(245,158,11,0.3); }
        .stat-card.red .stat-icon { background: #ef4444; color: #fff; box-shadow: 0 6px 16px rgba(239,68,68,0.3); }
        
        .stat-icon {
            width: 52px; height: 52px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center; font-size: 22px;
        }
        
        .stat-label { color: #6c757d; font-size: 13px; font-weight: 600; margin-bottom: 5px;}
        .stat-value { font-size: 28px; font-weight: 700; color: #1e3a5f; display:flex; align-items:baseline; gap: 6px;}
        .stat-value small { font-size: 14px; color: #6c757d; font-weight: 500; }
        
        /* Pendaftar Alert Card / Big Cards */
        .card-table {
            background: #fff; border-radius: 20px; padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            border-top: 5px solid #2d6a9f; /* Smart Door Accent */
        }
        .card-header-title {
            font-weight: 600; color: #1e3a5f; font-size: 16px;
            margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
        }
        /* Fix SweetAlert & Tambahan Efek Blur UI */
        .swal2-container {
            z-index: 99999 !important;
        }
        .swal2-container.swal2-backdrop-show, .modal-backdrop.show {
            backdrop-filter: blur(6px) !important;
            -webkit-backdrop-filter: blur(6px) !important;
            background-color: rgba(0, 0, 0, 0.4) !important;
            opacity: 1 !important;
        }

        /* Mobile Sidebar Toggle */
        .mobile-toggle {
            display: none;
            background: none; border: none; font-size: 24px; color: #1e3a5f;
        }
        .sidebar-overlay {
            display: none; position: fixed; top:0; left:0; width:100%; height:100%;
            background: rgba(0,0,0,0.5); z-index: 1035; backdrop-filter: blur(3px);
        }

        /* RESPONSIVE CSS */
        @media (max-width: 991px) {
            #sidebar {
                transform: translateX(-100%);
            }
            #sidebar.show {
                transform: translateX(0);
            }
            #main-content {
                margin-left: 0;
            }
            .mobile-toggle {
                display: block;
            }
            .topbar {
                padding: 15px 20px;
            }
            .topbar-left .page-title {
                font-size: 16px;
            }
            .sidebar-overlay.show {
                display: block;
            }
            .content-area {
                padding: 20px 15px;
            }
            .topbar-right .user-info {
                display: none; /* Hide name on small mobile, keep avatar */
            }
        }
    </style>
</head>
<body>

<!-- Sidebar Overlay untuk Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

{{-- SIDEBAR --}}
<div id="sidebar">
    <div class="brand">
        <h5><i class="bi bi-shield-lock"></i> Kos Bu Rini</h5>
        <small>Smart Door System</small>
    </div>
    <nav class="mt-3">
        @if(auth()->user()->role === 'admin')
            <div class="nav-section">Menu Utama</div>
            <a href="{{ route('admin.dashboard') }}"
               class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a>
            <a href="{{ route('admin.penghuni.index') }}"
            class="nav-link {{ request()->routeIs('admin.penghuni.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Data Penghuni
            </a>
            <a href="{{ route('admin.kamar.index') }}"
            class="nav-link {{ request()->routeIs('admin.kamar.*') ? 'active' : '' }}">
                <i class="bi bi-door-open"></i> Data Kamar
            </a>
            <a href="{{ route('admin.kartu.index') }}"
            class="nav-link {{ request()->requestUri === '/admin/kartu' || request()->routeIs('admin.kartu.*') ? 'active' : '' }}">
                <i class="bi bi-credit-card"></i> Manajemen Kartu
            </a>
            <a href="{{ route('admin.tagihan.index') }}"
               class="nav-link {{ request()->routeIs('admin.tagihan.*') ? 'active' : '' }}">
                <i class="bi bi-wallet2"></i> Tagihan & Pembayaran
                @php $menungguVerifikasi = \App\Models\Tagihan::where('status', 'menunggu_verifikasi')->count(); @endphp
                @if($menungguVerifikasi > 0)
                <span class="badge bg-warning text-dark ms-auto rounded-pill">{{ $menungguVerifikasi }}</span>
                @endif
            </a>
            <div class="nav-section">Monitoring</div>
            <a href="{{ route('admin.log.index') }}" class="nav-link {{ request()->routeIs('admin.log.*') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i> Log Aktivitas
            </a>
            @php $belumDilihat = \App\Models\PercobaanGagal::where('sudah_dilihat', false)->count(); @endphp
            <a href="{{ route('admin.percobaan.index') }}"
               class="nav-link {{ request()->routeIs('admin.percobaan.*') ? 'active' : '' }}"
               style="position:relative;">
                <i class="bi bi-shield-exclamation"></i> Percobaan Tidak Sah
                @if($belumDilihat > 0)
                <span style="
                    position:absolute; top:8px; right:14px;
                    background:#dc3545; color:#fff;
                    font-size:10px; font-weight:700;
                    min-width:18px; height:18px; line-height:18px;
                    border-radius:9px; text-align:center;
                    padding:0 5px;
                    box-shadow:0 2px 6px rgba(220,53,69,0.5);
                ">{{ $belumDilihat }}</span>
                @endif
            </a>
            @php $baruGangguan = \App\Models\LaporanGangguan::where('status', 'baru')->count(); @endphp
            <a href="{{ route('admin.laporan-gangguan.index') }}" class="nav-link {{ request()->routeIs('admin.laporan-gangguan.*') ? 'active' : '' }}" style="position:relative;">
                <i class="bi bi-tools"></i> Laporan Gangguan
                @if($baruGangguan > 0)
                <span style="
                    position:absolute; top:8px; right:14px;
                    background:#dc3545; color:#fff;
                    font-size:10px; font-weight:700;
                    min-width:18px; height:18px; line-height:18px;
                    border-radius:9px; text-align:center;
                    padding:0 5px;
                    box-shadow:0 2px 6px rgba(220,53,69,0.5);
                ">{{ $baruGangguan }}</span>
                @endif
            </a>
            @php $pendingLaporan = \App\Models\LaporanKehilangan::where('status', 'pending')->count(); @endphp
            <a href="{{ route('admin.laporan.index') }}"
               class="nav-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}"
               style="position:relative;">
                <i class="bi bi-credit-card-2-back"></i> Laporan Kehilangan
                @if($pendingLaporan > 0)
                <span style="
                    position:absolute; top:8px; right:14px;
                    background:#dc3545; color:#fff;
                    font-size:10px; font-weight:700;
                    min-width:18px; height:18px; line-height:18px;
                    border-radius:9px; text-align:center;
                    padding:0 5px;
                    box-shadow:0 2px 6px rgba(220,53,69,0.5);
                ">{{ $pendingLaporan }}</span>
                @endif
            </a>
            
            <div class="nav-section">Pengaturan</div>
            <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#modalUpdateWa">
                <i class="bi bi-whatsapp"></i> Update Nomor WA
            </a>
        @else
            <div class="nav-section">Menu</div>
            <a href="{{ route('penghuni.dashboard') }}"
               class="nav-link {{ request()->routeIs('penghuni.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a>
            <a href="{{ route('penghuni.akses') }}"
               class="nav-link {{ request()->routeIs('penghuni.akses') ? 'active' : '' }}">
                <i class="bi bi-key"></i> Akses Pintu
            </a>
            <a href="{{ route('penghuni.laporan-gangguan.index') }}"
               class="nav-link {{ request()->routeIs('penghuni.laporan-gangguan.*') ? 'active' : '' }}">
                <i class="bi bi-tools"></i> Laporan Gangguan
            </a>
            <a href="{{ route('penghuni.laporan.index') }}"
               class="nav-link {{ request()->routeIs('penghuni.laporan.*') ? 'active' : '' }}">
                <i class="bi bi-credit-card-2-back"></i> Lapor Kehilangan Kartu
            </a>
            <a href="{{ route('penghuni.tagihan.index') }}"
               class="nav-link {{ request()->routeIs('penghuni.tagihan.*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> Tagihan Saya
                @php
                    $belumBayar = 0;
                    if(auth()->user() && auth()->user()->penghuni) {
                        $belumBayar = \App\Models\Tagihan::where('penghuni_id', auth()->user()->penghuni->id)->where('status', 'belum_bayar')->count();
                    }
                @endphp
                @if($belumBayar > 0)
                <span class="badge bg-danger ms-auto rounded-pill">{{ $belumBayar }}</span>
                @endif
            </a>
            
            <div class="nav-section">Pengaturan</div>
            <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#modalUpdateWa">
                <i class="bi bi-whatsapp"></i> Update Nomor WA
            </a>
        @endif
    </nav>
    <div class="logout-area">
        <form method="POST" action="{{ route('logout') }}" id="logoutForm">
            @csrf
            <button type="button" onclick="confirmLogout()"
                class="nav-link w-100 border-0 bg-transparent text-start"
                style="color:rgba(255,255,255,0.7); cursor:pointer;">
                <i class="bi bi-box-arrow-left"></i> Logout
            </button>
        </form>
    </div>
</div>

{{-- MAIN CONTENT --}}
<div id="main-content">
    <div class="topbar">
        <div class="topbar-left">
            <button class="mobile-toggle me-3" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <div class="accent-line d-none d-md-block"></div>
            <div>
                <h6 class="page-title">@yield('page-title', 'Dashboard')</h6>
                <div class="page-subtitle">Smart Door System</div>
            </div>
        </div>
        
        <div class="topbar-right">
            <div class="user-info">
                <small>{{ auth()->user()->role }}</small>
                <strong>{{ auth()->user()->name }}</strong>
            </div>
            <div class="avatar">
                <i class="bi bi-person"></i>
            </div>
        </div>
    </div>
    <div class="content-area">
        @yield('content')
    </div>
</div>

@if(auth()->user())
@php
    $updateWaRoute = auth()->user()->role === 'admin' ? route('admin.update_nomor_hp') : route('penghuni.update_nomor_hp');
    $defaultPhone = auth()->user()->telepon;
    if (auth()->user()->role === 'admin' && empty($defaultPhone)) {
        $defaultPhone = env('ADMIN_WHATSAPP');
    }
@endphp
<!-- Modal Update WA -->
<div class="modal fade" id="modalUpdateWa" tabindex="-1" aria-labelledby="modalUpdateWaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; border:none; box-shadow:0 10px 40px rgba(0,0,0,0.1);">
            <form action="{{ $updateWaRoute }}" method="POST">
                @csrf
                <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d6a9f 100%); color:#fff; border-radius:16px 16px 0 0; padding:20px 24px; border-bottom:none;">
                    <h5 class="modal-title fw-bold" id="modalUpdateWaLabel" style="font-size:16px;">
                        <i class="bi bi-whatsapp me-2"></i>Pengaturan Nomor WhatsApp
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding:24px;">
                    <div class="alert alert-info" style="font-size:13px; border-radius:10px;">
                        <i class="bi bi-info-circle me-1"></i> 
                        @if(auth()->user()->role === 'admin')
                            Nomor ini akan ditampilkan di halaman Laporan Gangguan Penghuni agar mereka bisa menghubungi Anda secara langsung.
                        @else
                            Nomor ini digunakan untuk mengirimkan pemberitahuan sistem ke WhatsApp Anda.
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color:#1e3a5f;">Nomor WhatsApp <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone text-muted"></i></span>
                            <input type="text" name="telepon" class="form-control border-start-0 ps-0" placeholder="Contoh: 628123456789" required value="{{ $defaultPhone }}">
                        </div>
                        <div class="form-text mt-1" style="font-size:11px;">Gunakan format kode negara (contoh: 628...).</div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f0f0f0; padding:16px 24px;">
                    <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-dismiss="modal" style="border-radius:8px; font-weight:600; font-size:13px;">Batal</button>
                    <button type="submit" class="btn px-4 py-2" style="background:#1e3a5f; color:#fff; border-radius:8px; font-weight:600; font-size:13px;">Simpan Nomor</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

{{-- Modal Konfirmasi Logout dihapus dan diganti dengan SweetAlert --}}

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @if(session('register_success'))
    Swal.fire({
        icon: 'success',
        title: 'Registrasi Berhasil!',
        text: '{{ session('register_success') }}',
        confirmButtonColor: '#2d6a9f',
        confirmButtonText: 'Siap!',
        backdrop: `rgba(0,0,123,0.4)`,
        position: 'center'
    });
    @endif

    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{!! session('success') !!}',
        confirmButtonColor: '#2d6a9f',
        confirmButtonText: 'OK',
        position: 'center'
    });
    @endif

    @if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '{!! session('error') !!}',
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Tutup',
        position: 'center'
    });
    @endif

    function confirmLogout() {
        Swal.fire({
            title: 'Konfirmasi Logout',
            text: 'Apakah Anda yakin ingin keluar dari sistem?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Logout',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('logoutForm').submit();
            }
        });
    }
    function confirmSubmit(e, form, text) {
        e.preventDefault();
        Swal.fire({
            title: 'Konfirmasi',
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2d6a9f',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Lanjutkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }

    // Toggle Sidebar untuk Mobile
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    }
</script>

@stack('scripts')

</body>
</html>