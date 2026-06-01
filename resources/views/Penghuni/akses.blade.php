@extends('layouts.app')

@section('page-title', 'Akses Pintu Kamar')

@section('content')

@if(!$kamar)
<div class="alert alert-warning d-flex align-items-center">
    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
    <div>
        <strong>Anda belum mendapatkan kamar.</strong><br>
        Fitur akses pintu belum tersedia untuk Anda. Silakan hubungi admin untuk penetapan kamar.
    </div>
</div>
@else

@if(auth()->user()->penghuni->pin_aktif ?? false)
<div class="alert alert-info border-info d-flex align-items-center mb-4">
    <i class="bi bi-key-fill fs-3 text-info me-3"></i>
    <div>
        <strong>Akses PIN Keypad Aktif</strong><br>
        Anda dapat mengakses pintu kamar menggunakan kartu RFID Anda ataupun dengan memasukkan PIN 6 digit pada keypad.
    </div>
</div>
@endif

<div class="row g-4 mb-4">
    {{-- Card Status Pintu (Real-time) --}}
    <div class="col-md-5">
        <div class="card shadow-sm border-0 h-100" style="border-radius:12px; background:linear-gradient(135deg, #1e3a5f, #2d6a9f); color:white;">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-white-50 mb-1">Status Pintu Saat Ini</h6>
                        <h2 class="mb-0 fw-bold">Kamar {{ $kamar->nomor_kamar }}</h2>
                    </div>
                    <div id="doorIconWrap">
                        @if($kamar->status_pintu === 'terbuka')
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow" style="width:50px;height:50px;">
                            <i class="bi bi-door-open-fill fs-4"></i>
                        </div>
                        @else
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center shadow" style="width:50px;height:50px;">
                            <i class="bi bi-door-closed-fill fs-4"></i>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top border-white border-opacity-25">
                    <div id="doorStatusText">
                        @if($kamar->status_pintu === 'terbuka')
                        <div class="d-flex align-items-center bg-white bg-opacity-25 p-2 rounded">
                            <i class="bi bi-unlock-fill me-2 fs-5 text-warning"></i>
                            <span class="fw-semibold">Pintu dalam keadaan TERBUKA</span>
                        </div>
                        @else
                        <div class="d-flex align-items-center bg-white bg-opacity-25 p-2 rounded">
                            <i class="bi bi-lock-fill me-2 fs-5 text-info"></i>
                            <span class="fw-semibold">Pintu dalam keadaan TERTUTUP (Terkunci)</span>
                        </div>
                        @endif
                    </div>
                    <div class="mt-2 d-flex align-items-center justify-content-between">
                        <div class="text-white-50" style="font-size:12px;">
                            <i class="bi bi-clock-history me-1"></i>
                            <span id="lastAccess">{{ $kamar->terakhir_diakses ? $kamar->terakhir_diakses->diffForHumans() : 'Belum ada data' }}</span>
                        </div>
                        <div id="refreshIndicator" class="text-white-50" style="font-size:11px;display:none;">
                            <span class="spinner-border spinner-border-sm me-1" style="width:10px;height:10px;"></span> Memperbarui...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Remote Buka Pintu --}}
    <div class="col-md-7">
        <div class="card shadow-sm border-0 h-100" style="border-radius:12px;">
            <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center text-center">
                <i class="bi bi-phone-vibrate text-primary mb-3" style="font-size:40px;"></i>
                <h5 class="fw-bold text-dark">Remote Buka Pintu (Mobile)</h5>
                <p class="text-muted small mb-4">Anda dapat membuka pintu menggunakan tombol di bawah ini secara remote dari mana saja melalui koneksi internet.</p>
                <button class="btn btn-lg btn-primary rounded-pill px-5 fw-semibold shadow-sm" id="btnRemoteBukaPintu">
                    <i class="bi bi-fingerprint me-2"></i>Buka Pintu Sekarang
                </button>
                <div id="remoteResponse" class="mt-3 small" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>

{{-- Real-time polling script --}}
<script>
const KAMAR_ID   = {{ $kamar->id }};
const STATUS_URL = `/api/iot/status-pintu/${KAMAR_ID}`;

function updateDoorUI(data) {
    const isTerbuka = data.status_pintu === 'terbuka';

    document.getElementById('doorIconWrap').innerHTML = isTerbuka
        ? `<div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow" style="width:50px;height:50px;">
               <i class="bi bi-door-open-fill fs-4"></i></div>`
        : `<div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center shadow" style="width:50px;height:50px;">
               <i class="bi bi-door-closed-fill fs-4"></i></div>`;

    document.getElementById('doorStatusText').innerHTML = isTerbuka
        ? `<div class="d-flex align-items-center bg-white bg-opacity-25 p-2 rounded">
               <i class="bi bi-unlock-fill me-2 fs-5 text-warning"></i>
               <span class="fw-semibold">Pintu dalam keadaan TERBUKA</span></div>`
        : `<div class="d-flex align-items-center bg-white bg-opacity-25 p-2 rounded">
               <i class="bi bi-lock-fill me-2 fs-5 text-info"></i>
               <span class="fw-semibold">Pintu dalam keadaan TERTUTUP (Terkunci)</span></div>`;

    if (data.updated_at) {
        document.getElementById('lastAccess').textContent = 'Diperbarui: ' + data.updated_at;
    }
}

async function pollStatus() {
    const ind = document.getElementById('refreshIndicator');
    ind.style.display = 'block';
    try {
        const res  = await fetch(STATUS_URL);
        const data = await res.json();
        if (data.status_pintu) updateDoorUI(data);
    } catch(e) { /* silent fail */ }
    finally { ind.style.display = 'none'; }
}

// Poll setiap 3 detik
setInterval(pollStatus, 3000);

// Integrasi Buka Pintu via Web
document.getElementById('btnRemoteBukaPintu').addEventListener('click', async function() {
    const btn = this;
    const resBox = document.getElementById('remoteResponse');
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
    resBox.style.display = 'none';

    try {
        const response = await fetch("{{ route('penghuni.akses.buka_pintu') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        const data = await response.json();
        
        resBox.style.display = 'block';
        if(data.success) {
            resBox.className = 'mt-3 small text-success fw-bold';
            resBox.innerHTML = '<i class="bi bi-check-circle"></i> ' + data.message;
            pollStatus(); // force refresh status
        } else {
            resBox.className = 'mt-3 small text-danger fw-bold';
            resBox.innerHTML = '<i class="bi bi-x-circle"></i> ' + data.message;
        }
    } catch (e) {
        resBox.style.display = 'block';
        resBox.className = 'mt-3 small text-danger fw-bold';
        resBox.innerHTML = '<i class="bi bi-x-circle"></i> Terjadi kesalahan jaringan.';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-fingerprint me-2"></i>Buka Pintu Sekarang';
    }
});
</script>


<div class="card-table">
    <div class="card-header-title d-flex justify-content-between align-items-center">
        <div><i class="bi bi-clock-history"></i> Riwayat Akses Kamar Anda</div>
        <button class="btn btn-sm btn-outline-secondary" onclick="window.location.reload();">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Waktu</th>
                <th>Aksi</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td>
                    <div class="fw-semibold">{{ $log->waktu->format('d M Y') }}</div>
                    <div class="text-muted" style="font-size: 12px;">{{ $log->waktu->format('H:i:s') }}</div>
                </td>
                <td>
                    @if($log->aksi === 'masuk')
                        <span class="badge bg-success"><i class="bi bi-box-arrow-in-right"></i> Masuk</span>
                    @else
                        <span class="badge bg-danger"><i class="bi bi-box-arrow-right"></i> Keluar</span>
                    @endif
                </td>
                <td>
                    @if($log->status === 'berhasil')
                        <span class="text-success"><i class="bi bi-check-circle-fill"></i> Berhasil</span>
                    @else
                        <span class="text-danger"><i class="bi bi-x-circle-fill"></i> Ditolak</span>
                    @endif
                </td>
                <td><small class="text-muted">{{ $log->keterangan ?? '-' }}</small></td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center text-muted py-4">
                    <i class="bi bi-inbox" style="font-size:30px"></i>
                    <br>Belum ada aktivitas tercatat untuk kamar Anda.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($logs->hasPages())
    <div class="mt-4">
        {{ $logs->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endif
@endsection
