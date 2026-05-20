@extends('layouts.app')
@section('page-title', 'Laporan Gangguan')
@section('content')
<style>
.page-header{display:flex;align-items:center;gap:12px;margin-bottom:24px;}
.header-icon{width:46px;height:46px;background:linear-gradient(135deg,#fff3cd,#ffc107);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#856404;font-size:22px;}
.page-header h5{font-weight:700;color:#1e3a5f;margin:0;}
.stat-mini{background:#fff;border-radius:12px;padding:16px 20px;box-shadow:0 2px 10px rgba(0,0,0,0.06);border-left:4px solid;display:flex;align-items:center;gap:14px;}
.stat-mini.red{border-color:#dc3545;} .stat-mini.yellow{border-color:#ffc107;} .stat-mini.green{border-color:#28a745;} .stat-mini.orange{border-color:#fd7e14;}
.stat-mini-icon{width:40px;height:40px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:18px;}
.stat-mini-icon.red{background:#fde8e8;color:#dc3545;} .stat-mini-icon.yellow{background:#fff8e1;color:#856404;} .stat-mini-icon.green{background:#e8f5ea;color:#28a745;} .stat-mini-icon.orange{background:#fef3e8;color:#fd7e14;}
.stat-mini-val{font-size:24px;font-weight:700;color:#1e3a5f;line-height:1;}
.stat-mini-lbl{font-size:11px;color:#888;}
.filter-card{background:#fff;border-radius:12px;padding:16px 20px;box-shadow:0 2px 10px rgba(0,0,0,0.06);margin-bottom:20px;}
.table-card{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06);overflow:hidden;}
.table-card .table{margin-bottom:0;}
.table-card .table thead th{background:#f8f9fa;color:#1e3a5f;font-weight:600;font-size:13px;border-bottom:2px solid #e9ecef;padding:12px 14px;white-space:nowrap;}
.table-card .table tbody td{padding:12px 14px;vertical-align:middle;font-size:13px;}
.badge-baru{background:#fde8e8;color:#dc3545;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.badge-diproses{background:#fff3cd;color:#856404;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.badge-selesai{background:#d1f0da;color:#157347;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.badge-mendesak{background:#dc3545;color:#fff;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;}
.badge-normal-urg{background:#6c757d;color:#fff;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;}
.no-lp{background:#f1f5f9;padding:3px 8px;border-radius:6px;font-family:monospace;font-size:12px;color:#1e3a5f;font-weight:700;}
.action-btn{padding:4px 12px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;transition:all .15s;text-decoration:none;display:inline-block;}
.action-btn.proses{background:#fff3cd;color:#856404;} .action-btn.proses:hover{background:#ffc107;color:#fff;}
.action-btn.selesai{background:#d1f0da;color:#157347;} .action-btn.selesai:hover{background:#28a745;color:#fff;}
.action-btn.detail{background:#e8f0f8;color:#2d6a9f;} .action-btn.detail:hover{background:#2d6a9f;color:#fff;}
.action-btn.hapus{background:#fde8e8;color:#dc3545;} .action-btn.hapus:hover{background:#dc3545;color:#fff;}
.empty-state{text-align:center;padding:60px 20px;color:#aaa;}
.empty-state i{font-size:48px;display:block;margin-bottom:12px;}
</style>

<div class="page-header">
    <div class="header-icon"><i class="bi bi-tools"></i></div>
    <div>
        <h5>Laporan Gangguan</h5>
        <div class="text-muted" style="font-size:13px;">Kelola semua laporan gangguan dari penghuni</div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="stat-mini red">
            <div class="stat-mini-icon red"><i class="bi bi-exclamation-circle-fill"></i></div>
            <div><div class="stat-mini-val">{{ $stats['baru'] }}</div><div class="stat-mini-lbl">Baru</div></div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-mini yellow">
            <div class="stat-mini-icon yellow"><i class="bi bi-arrow-repeat"></i></div>
            <div><div class="stat-mini-val">{{ $stats['diproses'] }}</div><div class="stat-mini-lbl">Diproses</div></div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-mini green">
            <div class="stat-mini-icon green"><i class="bi bi-check-circle-fill"></i></div>
            <div><div class="stat-mini-val">{{ $stats['selesai'] }}</div><div class="stat-mini-lbl">Selesai</div></div>
        </div>
    </div>
</div>

<div class="filter-card">
    <form method="GET" action="{{ route('admin.laporan-gangguan.index') }}" class="row g-2 align-items-end">
        <div class="col-12 col-md-3">
            <label class="form-label fw-semibold mb-1" style="font-size:12px;">Cari</label>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="No. laporan / nama / kamar..." value="{{ request('search') }}">
        </div>
        <div class="col-4 col-md-2">
            <label class="form-label fw-semibold mb-1" style="font-size:12px;">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua</option>
                <option value="baru" {{ request('status')==='baru'?'selected':'' }}>Baru</option>
                <option value="diproses" {{ request('status')==='diproses'?'selected':'' }}>Diproses</option>
                <option value="selesai" {{ request('status')==='selesai'?'selected':'' }}>Selesai</option>
            </select>
        </div>

        <div class="col-4 col-md-3">
            <label class="form-label fw-semibold mb-1" style="font-size:12px;">Kategori</label>
            <select name="kategori" class="form-select form-select-sm">
                <option value="">Semua Kategori</option>
                @foreach(['Listrik','Air','Furnitur','Pintu & Kunci','Internet','Lainnya'] as $kat)
                <option value="{{ $kat }}" {{ request('kategori')===$kat?'selected':'' }}>{{ $kat }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm px-3 w-100"><i class="bi bi-search me-1"></i>Filter</button>
            <a href="{{ route('admin.laporan-gangguan.index') }}" class="btn btn-outline-secondary btn-sm px-2"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>
</div>

<div class="table-card">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>No. Laporan</th>
                <th>Penghuni</th>
                <th>Kamar</th>
                <th>Kategori</th>

                <th>Status</th>
                <th>Tanggal</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporan as $lap)
            <tr>
                <td><code class="no-lp">{{ $lap->no_laporan }}</code></td>
                <td style="font-weight:600;color:#1e3a5f;">{{ $lap->nama_penghuni }}</td>
                <td><span style="background:#e8f0f8;color:#2d6a9f;padding:2px 10px;border-radius:10px;font-size:12px;font-weight:700;">{{ $lap->no_kamar }}</span></td>
                <td>
                    @php $katIcon = match($lap->kategori) { 'Listrik'=>'bi-lightning-charge','Air'=>'bi-droplet','Furnitur'=>'bi-box','Pintu & Kunci'=>'bi-door-open','Internet'=>'bi-wifi',default=>'bi-three-dots' }; @endphp
                    <i class="bi {{ $katIcon }} me-1 text-muted"></i>{{ $lap->kategori }}
                </td>

                <td>
                    @if($lap->status==='baru')
                        <span class="badge-baru"><i class="bi bi-circle-fill me-1" style="font-size:7px;"></i>Baru</span>
                    @elseif($lap->status==='diproses')
                        <span class="badge-diproses"><i class="bi bi-arrow-repeat me-1"></i>Diproses</span>
                    @else
                        <span class="badge-selesai"><i class="bi bi-check-circle me-1"></i>Selesai</span>
                    @endif
                </td>
                <td>
                    <div style="font-size:13px;">{{ $lap->created_at->format('d/m/Y') }}</div>
                    <div class="text-muted" style="font-size:11px;">{{ $lap->created_at->format('H:i') }}</div>
                </td>
                <td class="text-center">
                    <div class="d-flex gap-1 justify-content-center flex-wrap">
                        <a href="{{ route('admin.laporan-gangguan.show', $lap->id) }}" class="action-btn detail"><i class="bi bi-eye me-1"></i>Detail</a>
                        @if($lap->status==='baru')
                        <form method="POST" action="{{ route('admin.laporan-gangguan.proses', $lap->id) }}" class="d-inline" id="proses-{{ $lap->id }}">
                            @csrf @method('PATCH')
                            <button type="button" class="action-btn proses" onclick="konfirmasiProses({{ $lap->id }})"><i class="bi bi-arrow-repeat me-1"></i>Proses</button>
                        </form>
                        @endif
                        @if($lap->status!=='selesai')
                        <button type="button" class="action-btn selesai" onclick="konfirmasiSelesai({{ $lap->id }},'{{ $lap->no_laporan }}')"><i class="bi bi-check2 me-1"></i>Selesai</button>
                        @endif
                        <form method="POST" action="{{ route('admin.laporan-gangguan.destroy',$lap->id) }}" class="d-inline" id="del-{{ $lap->id }}">
                            @csrf @method('DELETE')
                            <button type="button" class="action-btn hapus" onclick="konfirmasiHapus({{ $lap->id }})"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i>Belum ada laporan gangguan.@if(request()->hasAny(['status','kategori','search']))<br><a href="{{ route('admin.laporan-gangguan.index') }}" class="btn btn-sm btn-outline-primary mt-2">Reset Filter</a>@endif</div></td></tr>
            @endforelse
        </tbody>
    </table>
    @if($laporan->hasPages())
    <div class="px-4 py-3 border-top">{{ $laporan->links() }}</div>
    @endif
</div>

{{-- Modal Selesai --}}
<div class="modal fade" id="modalSelesai" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:linear-gradient(135deg,#1e3a5f,#2d6a9f);border-radius:16px 16px 0 0;border-bottom:none;">
                <h6 class="modal-title text-white fw-bold"><i class="bi bi-check-circle me-2"></i>Tandai Selesai</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formSelesai">
                @csrf @method('PATCH')
                <div class="modal-body p-4">
                    <p class="text-muted mb-3" style="font-size:13px;">Laporan <strong id="noLapSelesai"></strong> akan ditandai <strong>Selesai</strong>. Notifikasi WA dikirim ke penghuni.</p>
                    <div>
                        <label class="form-label fw-semibold" style="font-size:13px;">Catatan Admin <span class="text-muted fw-normal">(opsional)</span></label>
                        <textarea name="catatan_admin" class="form-control" rows="3" placeholder="Contoh: Diperbaiki teknisi pukul 14.00..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-check-circle me-1"></i>Tandai Selesai</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function konfirmasiSelesai(id, noLap) {
    document.getElementById('formSelesai').action = `/admin/laporan-gangguan/${id}/selesai`;
    document.getElementById('noLapSelesai').textContent = noLap;
    new bootstrap.Modal(document.getElementById('modalSelesai')).show();
}
function konfirmasiProses(id) {
    Swal.fire({
        title: 'Tandai Diproses?',
        text: 'Laporan ini akan ditandai sedang diproses.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Proses!',
        cancelButtonText: 'Batal',
        position: 'center'
    }).then(r => { if (r.isConfirmed) document.getElementById('proses-' + id).submit(); });
}
function konfirmasiHapus(id) {
    Swal.fire({
        title: 'Hapus Laporan?',
        text: 'Data akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        position: 'center'
    }).then(r => { if (r.isConfirmed) document.getElementById('del-' + id).submit(); });
}
</script>
@endpush
@endsection
