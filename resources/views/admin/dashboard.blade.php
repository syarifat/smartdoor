@extends('layouts.app')

@section('page-title', 'Dashboard Admin')

@section('content')

<style>
.stat-card-clickable {
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card-clickable:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
}
.stat-card-clickable .click-hint {
    font-size: 10px;
    color: #bbb;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 3px;
}
.stat-card-clickable:hover .click-hint { color: #888; }

@keyframes pulse-amber {
    0%   { box-shadow: 0 0 0 0 rgba(245,158,11,0.5); }
    70%  { box-shadow: 0 0 0 10px rgba(245,158,11,0); }
    100% { box-shadow: 0 0 0 0 rgba(245,158,11,0); }
}

/* Modal table */
.modal-table th { font-size: 12px; font-weight: 600; color: #555; }
.modal-table td { font-size: 13px; vertical-align: middle; }
.modal-table tbody tr:hover { background: #f8f9ff; }

/* Badge kamar di modal */
.badge-kamar { font-size: 11px; }

/* Anggota chip */
.chip-anggota {
    display: inline-block;
    background: #e8f0f8;
    color: #1e3a5f;
    font-size: 10px;
    padding: 2px 8px;
    border-radius: 20px;
    margin: 1px;
    font-weight: 500;
}
</style>

{{-- STAT CARDS ROW 1 --}}
<div class="row g-4 mb-3">

    {{-- Total Penghuni --}}
    <div class="col-md-3">
        <div class="stat-card blue stat-card-clickable" data-bs-toggle="modal" data-bs-target="#modalPenghuni">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Total Penghuni</div>
                    <div class="stat-value">{{ $totalPenghuni ?? 0 }}</div>
                </div>
                <div class="stat-icon"><i class="bi bi-people"></i></div>
            </div>
            <div class="mt-3">
                <span class="badge" style="background:#e8f0f8; color:#2d6a9f; padding:6px 10px; font-size:10px; border-radius:8px; letter-spacing:0.5px;">{{ $totalKK ?? 0 }} KK (Kepala Keluarga)</span>
            </div>
            <div class="click-hint" style="margin-top:8px; font-size:10px; color:#888;"><i class="bi bi-eye"></i> Klik untuk detail</div>
        </div>
    </div>

    {{-- Kamar Terisi --}}
    <div class="col-md-3">
        <div class="stat-card green stat-card-clickable" data-bs-toggle="modal" data-bs-target="#modalKamarTerisi">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Kamar Terisi</div>
                    <div class="stat-value">{{ $kamarTerisi ?? 0 }}</div>
                </div>
                <div class="stat-icon"><i class="bi bi-door-closed"></i></div>
            </div>
            <div class="mt-3">
                <span class="badge" style="background:#e8f5ea; color:#28a745; padding:6px 10px; font-size:10px; border-radius:8px; letter-spacing:0.5px;">Aktif Ditempati</span>
            </div>
            <div class="click-hint" style="margin-top:8px; font-size:10px; color:#888;"><i class="bi bi-eye"></i> Klik untuk detail</div>
        </div>
    </div>

    {{-- Kamar Kosong --}}
    <div class="col-md-3">
        <div class="stat-card orange stat-card-clickable" data-bs-toggle="modal" data-bs-target="#modalKamarKosong">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Kamar Kosong</div>
                    <div class="stat-value">{{ $kamarKosong ?? 0 }}</div>
                </div>
                <div class="stat-icon"><i class="bi bi-door-open"></i></div>
            </div>
            <div class="mt-3">
                <span class="badge" style="background:#fef3e8; color:#fd7e14; padding:6px 10px; font-size:10px; border-radius:8px; letter-spacing:0.5px;">Siap Dihuni</span>
            </div>
            <div class="click-hint" style="margin-top:8px; font-size:10px; color:#888;"><i class="bi bi-eye"></i> Klik untuk detail</div>
        </div>
    </div>

    {{-- Total Kamar --}}
    <div class="col-md-3">
        <div class="stat-card red stat-card-clickable" data-bs-toggle="modal" data-bs-target="#modalSemuaKamar">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Total Kamar</div>
                    <div class="stat-value">{{ $totalKamar ?? 0 }}</div>
                </div>
                <div class="stat-icon"><i class="bi bi-building"></i></div>
            </div>
            <div class="mt-3">
                <span class="badge" style="background:#fde8e8; color:#dc3545; padding:6px 10px; font-size:10px; border-radius:8px; letter-spacing:0.5px;">Keseluruhan Unit</span>
            </div>
            <div class="click-hint" style="margin-top:8px; font-size:10px; color:#888;"><i class="bi bi-eye"></i> Klik untuk detail</div>
        </div>
    </div>

</div>

{{-- STAT CARD ROW 2: Pendaftar Menunggu --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card orange stat-card-clickable" data-bs-toggle="modal" data-bs-target="#modalPendaftar">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Menunggu Diproses</div>
                    <div class="stat-value">{{ $pendaftarMenunggu ?? 0 }}</div>
                </div>
                <div class="stat-icon"><i class="bi bi-person-exclamation"></i></div>
            </div>
            <div class="mt-3">
                <span class="badge" style="background:#fef3e8; color:#fd7e14; padding:6px 10px; font-size:10px; border-radius:8px; letter-spacing:0.5px;">Pendaftar Baru</span>
            </div>
            <div class="click-hint" style="margin-top:8px; font-size:10px; color:#888;"><i class="bi bi-eye"></i> Klik untuk lihat detail</div>
        </div>
    </div>
</div>

{{-- LOG AKTIVITAS TERBARU --}}
<div class="card-table">
    <div class="card-header-title">
        <i class="bi bi-clock-history"></i> Aktivitas Akses Pintu Terbaru
    </div>
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Penghuni</th>
                <th>Kamar</th>
                <th>Aktivitas</th>
                <th>Status Pintu</th>
                <th>Waktu</th>
            </tr>
        </thead>
        <tbody id="logsTableBody">
            @forelse($recentLogs as $i => $log)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>
                    @if($log->penghuni)
                        <div class="fw-semibold">{{ $log->penghuni->nama }}</div>
                    @else
                        <span class="text-muted">Tidak Dikenal (UID: {{ $log->uid }})</span>
                    @endif
                </td>
                <td>
                    @if($log->kamar)
                        <span class="badge bg-primary">Kamar {{ $log->kamar->nomor_kamar }}</span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
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
                        <span class="text-danger" title="{{ $log->keterangan }}"><i class="bi bi-x-circle-fill"></i> Ditolak</span>
                    @endif
                </td>
                <td><small class="text-muted">{{ $log->waktu->diffForHumans() }}</small></td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    <i class="bi bi-inbox" style="font-size:30px"></i>
                    <br>Belum ada aktivitas tercatat
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
// ── Auto-refresh aktivitas terbaru setiap 10 detik ──────────────
const AKTIVITAS_URL = '{{ route("admin.api.aktivitas") }}';

function buildStatusBadge(status) {
    return status === 'berhasil'
        ? `<span class="text-success"><i class="bi bi-check-circle-fill"></i> Berhasil</span>`
        : `<span class="text-danger"><i class="bi bi-x-circle-fill"></i> Ditolak</span>`;
}

function buildAksiBadge(aksi) {
    return aksi === 'masuk'
        ? `<span class="badge bg-success"><i class="bi bi-box-arrow-in-right"></i> Masuk</span>`
        : `<span class="badge bg-danger"><i class="bi bi-box-arrow-right"></i> Keluar</span>`;
}

async function refreshAktivitas() {
    try {
        const res  = await fetch(AKTIVITAS_URL);
        const json = await res.json();

        // Update tabel log
        const tbody = document.getElementById('logsTableBody');
        if (tbody && json.logs.length > 0) {
            tbody.innerHTML = json.logs.map((log, i) => `
                <tr>
                    <td>${i+1}</td>
                    <td><div class="fw-semibold" style="font-size:13px;">${log.penghuni}</div></td>
                    <td>${log.kamar !== '-' ? `<span class="badge bg-primary">${log.kamar}</span>` : '<span class="text-muted">-</span>'}</td>
                    <td>${buildAksiBadge(log.aksi)}</td>
                    <td>${buildStatusBadge(log.status)}</td>
                    <td><small class="text-muted">${log.waktu}</small></td>
                </tr>`).join('');
        }

        // Update badge percobaan gagal di sidebar
        const badgeEl = document.getElementById('badgePercobaanSidebar');
        if (badgeEl) {
            if (json.belum_dilihat > 0) {
                badgeEl.textContent = json.belum_dilihat;
                badgeEl.style.display = 'inline-block';
            } else {
                badgeEl.style.display = 'none';
            }
        }
    } catch(e) { /* silent fail */ }
}

setInterval(refreshAktivitas, 10000);
</script>



{{-- ============================================================ --}}
{{-- MODAL 1: Total Penghuni                                      --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalPenghuni" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(135deg,#1e3a5f,#2d6a9f);">
        <h5 class="modal-title text-white">
          <i class="bi bi-people me-2"></i>Daftar Penghuni ({{ $totalPenghuni ?? 0 }} Orang / {{ $totalKK ?? 0 }} KK)
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <table class="table table-hover mb-0 modal-table">
          <thead class="table-light">
            <tr>
              <th class="ps-3">#</th>
              <th>Nama</th>
              <th>Kamar</th>
              <th>Telepon</th>
              <th>Anggota</th>
            </tr>
          </thead>
          <tbody>
            @forelse($penghuniList as $i => $p)
            <tr>
              <td class="ps-3 text-muted">{{ $i+1 }}</td>
              <td>
                <div class="fw-semibold">{{ $p->nama }}</div>
                <div style="font-size:11px;color:#aaa;">Kepala keluarga</div>
              </td>
              <td>
                @if($p->kamar)
                  <span class="badge bg-primary badge-kamar">Kamar {{ $p->kamar->nomor_kamar }}</span>
                @else
                  <span class="text-muted small">-</span>
                @endif
              </td>
              <td><small>{{ $p->telepon ?? '-' }}</small></td>
              <td>
                @if($p->anggotaKeluargas && $p->anggotaKeluargas->count() > 0)
                  @foreach($p->anggotaKeluargas as $ag)
                    <span class="chip-anggota">{{ $ag->nama }} <span style="opacity:.6;">({{ $ag->hubungan }})</span></span>
                  @endforeach
                @else
                  <span class="text-muted small">-</span>
                @endif
              </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted py-4">Belum ada penghuni</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <a href="{{ route('admin.penghuni.index') }}" class="btn btn-sm btn-primary">
          <i class="bi bi-arrow-right me-1"></i>Kelola Data Penghuni
        </a>
        <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>


{{-- ============================================================ --}}
{{-- MODAL 2: Kamar Terisi                                        --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalKamarTerisi" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(135deg,#166534,#16a34a);">
        <h5 class="modal-title text-white">
          <i class="bi bi-door-closed me-2"></i>Kamar Terisi ({{ $kamarTerisi ?? 0 }})
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <table class="table table-hover mb-0 modal-table">
          <thead class="table-light">
            <tr>
              <th class="ps-3">#</th>
              <th>Nomor Kamar</th>
              <th>Penghuni</th>
              <th>Status Pintu</th>
              <th>Terakhir Diakses</th>
            </tr>
          </thead>
          <tbody>
            @forelse($kamarTerisiList as $i => $kamar)
            <tr>
              <td class="ps-3 text-muted">{{ $i+1 }}</td>
              <td><span class="badge bg-success">Kamar {{ $kamar->nomor_kamar }}</span></td>
              <td>
                @foreach($kamar->penghuni as $p)
                  <div class="fw-semibold" style="font-size:13px;">{{ $p->nama }}</div>
                  @if($p->anggotaKeluargas && $p->anggotaKeluargas->count() > 0)
                    @foreach($p->anggotaKeluargas as $ag)
                      <span class="chip-anggota">{{ $ag->nama }}</span>
                    @endforeach
                  @endif
                @endforeach
              </td>
              <td>
                @if($kamar->status_pintu === 'terbuka')
                    <span class="badge bg-danger"><i class="bi bi-door-open-fill"></i> Terbuka</span>
                @else
                    <span class="badge bg-success"><i class="bi bi-door-closed-fill"></i> Tertutup</span>
                @endif
              </td>
              <td>
                @if($kamar->terakhir_diakses)
                    <div style="font-size:12px;">{{ $kamar->terakhir_diakses->diffForHumans() }}</div>
                    <div class="text-muted" style="font-size:10px;">{{ $kamar->terakhir_diakses->format('d/m/y H:i:s') }}</div>
                @else
                    <span class="text-muted small">-</span>
                @endif
              </td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada kamar terisi</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>


{{-- ============================================================ --}}
{{-- MODAL 3: Kamar Kosong                                        --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalKamarKosong" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(135deg,#92400e,#d97706);">
        <h5 class="modal-title text-white">
          <i class="bi bi-door-open me-2"></i>Kamar Kosong ({{ $kamarKosong ?? 0 }})
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <table class="table table-hover mb-0 modal-table">
          <thead class="table-light">
            <tr>
              <th class="ps-3">#</th>
              <th>Nomor Kamar</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($kamarKosongList as $i => $kamar)
            <tr>
              <td class="ps-3 text-muted">{{ $i+1 }}</td>
              <td><strong>Kamar {{ $kamar->nomor_kamar }}</strong></td>
              <td><span class="badge bg-success">Tersedia</span></td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-center text-muted py-4">Tidak ada kamar kosong</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <a href="{{ route('admin.kamar.index') }}" class="btn btn-sm btn-warning">
          <i class="bi bi-gear me-1"></i>Kelola Kamar
        </a>
        <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>


{{-- ============================================================ --}}
{{-- MODAL 4: Semua Kamar                                         --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalSemuaKamar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(135deg,#7f1d1d,#dc2626);">
        <h5 class="modal-title text-white">
          <i class="bi bi-building me-2"></i>Semua Kamar ({{ $totalKamar ?? 0 }})
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <table class="table table-hover mb-0 modal-table">
          <thead class="table-light">
            <tr>
              <th class="ps-3">#</th>
              <th>Nomor Kamar</th>
              <th>Penyewaan</th>
              <th>Status Pintu</th>
              <th>Terakhir Diakses</th>
            </tr>
          </thead>
          <tbody>
            @forelse($kamarList as $i => $kamar)
            <tr>
              <td class="ps-3 text-muted">{{ $i+1 }}</td>
              <td><strong>Kamar {{ $kamar->nomor_kamar }}</strong></td>
              <td>
                @if($kamar->status === 'terisi')
                  <span class="badge bg-primary">Terisi</span>
                @else
                  <span class="badge bg-success">Tersedia</span>
                @endif
              </td>
              <td>
                @if($kamar->status_pintu === 'terbuka')
                    <span class="badge bg-danger"><i class="bi bi-door-open-fill"></i> Terbuka</span>
                @else
                    <span class="badge bg-secondary"><i class="bi bi-door-closed-fill"></i> Tertutup</span>
                @endif
              </td>
              <td>
                @if($kamar->terakhir_diakses)
                    <div style="font-size:12px;">{{ $kamar->terakhir_diakses->diffForHumans() }}</div>
                @else
                    <span class="text-muted small">-</span>
                @endif
              </td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada kamar</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <a href="{{ route('admin.kamar.index') }}" class="btn btn-sm btn-danger">
          <i class="bi bi-arrow-right me-1"></i>Kelola Kamar
        </a>
        <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>


{{-- ============================================================ --}}
{{-- MODAL 5: Pendaftar Belum Dapat Kamar                         --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalPendaftar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(135deg,#92400e,#f59e0b);">
        <h5 class="modal-title text-white">
          <i class="bi bi-person-exclamation me-2"></i>Pendaftar Menunggu ({{ $pendaftarMenunggu ?? 0 }})
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        @if(($pendaftarMenunggu ?? 0) > 0)
        <div class="alert alert-warning m-3 mb-0" style="font-size:13px;">
          <i class="bi bi-info-circle me-1"></i>
          Pengguna berikut sudah mendaftar akun tapi belum diproses. Klik <strong>Proses</strong> untuk menambahkan ke data penghuni dan assign kamar.
        </div>
        @endif
        <table class="table table-hover mb-0 modal-table">
          <thead class="table-light">
            <tr>
              <th class="ps-3">#</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Telepon</th>
              <th>Daftar</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($pendaftarMenungguList as $i => $user)
            <tr>
              <td class="ps-3 text-muted">{{ $i+1 }}</td>
              <td><div class="fw-semibold">{{ $user->name }}</div></td>
              <td><small class="text-muted">{{ $user->email }}</small></td>
              <td><small>{{ $user->telepon ?? '-' }}</small></td>
              <td><small class="text-muted">{{ $user->created_at->diffForHumans() }}</small></td>
              <td>
                <a href="{{ route('admin.penghuni.create') }}?user_id={{ $user->id }}"
                   class="btn btn-warning btn-sm" style="font-size:11px;">
                  <i class="bi bi-person-plus"></i> Proses
                </a>
                <form action="{{ route('admin.pendaftar.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="confirmSubmit(event, this, 'Yakin hapus pendaftar ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger btn-sm" style="font-size:11px;">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-4">
                <i class="bi bi-check-circle" style="font-size:28px;color:#22c55e;"></i>
                <br><span style="color:#22c55e;font-weight:600;">Semua pendaftar sudah diproses!</span>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

@endsection