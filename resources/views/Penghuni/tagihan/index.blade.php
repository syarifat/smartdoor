@extends('layouts.app')

@section('page-title', 'Tagihan Saya')

@section('content')
<div class="card-table">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="card-header-title m-0"><i class="bi bi-receipt"></i> Daftar Tagihan Kamar</h5>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        {{-- TABEL TAGIHAN AKTIF --}}
        <div class="col-12 mb-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-exclamation-circle text-warning"></i> Tagihan Aktif</h6>
            <div class="table-responsive bg-white rounded shadow-sm border">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Bulan / Keterangan</th>
                            <th>Tanggal Tagihan</th>
                            <th>Kamar</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $tagihanAktif = $tagihans->where('status', '!=', 'lunas');
                        @endphp
                        @forelse($tagihanAktif as $t)
                        <tr>
                            <td>
                                <div class="fw-bold d-flex align-items-center gap-2">
                                    {{ $t->bulan }}
                                    @if($t->keterangan && str_contains($t->keterangan, 'Denda kehilangan kartu'))
                                        <span class="badge bg-danger" style="font-size:10px;">
                                            <i class="bi bi-exclamation-triangle-fill"></i> Denda Kartu
                                        </span>
                                    @endif
                                </div>
                                @if($t->keterangan)
                                    <div class="text-muted small mt-1" style="max-width: 220px; white-space: normal;">
                                        <i class="bi bi-info-circle"></i> {{ $t->keterangan }}
                                    </div>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($t->tanggal_tagihan)->format('d M Y') }}</td>
                            <td>Kamar {{ $t->kamar->nomor_kamar ?? '-' }}</td>
                            <td>Rp {{ number_format($t->jumlah_tagihan, 0, ',', '.') }}</td>
                            <td>
                                @if($t->status == 'belum_bayar')
                                    <span class="badge bg-danger">Belum Bayar</span>
                                @elseif($t->status == 'menunggu_verifikasi')
                                    <span class="badge bg-warning text-dark">Menunggu Verifikasi</span>
                                @endif
                            </td>
                            <td>
                                @if($t->status == 'belum_bayar')
                                    <form action="{{ route('penghuni.tagihan.bayar', $t->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="bi bi-credit-card"></i> Bayar Midtrans
                                        </button>
                                    </form>
                                @elseif($t->status == 'menunggu_verifikasi')
                                    <div class="d-flex gap-2 flex-wrap">
                                        <form action="{{ route('penghuni.tagihan.bayar', $t->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning text-dark">
                                                <i class="bi bi-arrow-clockwise"></i> Cek Status / Lanjut Midtrans
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm {{ $t->bukti_pembayaran ? 'btn-outline-secondary' : 'btn-outline-primary' }}" data-bs-toggle="modal" data-bs-target="#uploadModal{{ $t->id }}">
                                            <i class="bi bi-upload"></i> {{ $t->bukti_pembayaran ? 'Upload Ulang Bukti' : 'Upload Bukti Pembayaran' }}
                                        </button>
                                        @if($t->bukti_pembayaran)
                                        <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#viewBuktiModal{{ $t->id }}">
                                            <i class="bi bi-card-image"></i> Lihat Bukti
                                        </button>
                                        @endif
                                    </div>

                                    @if($t->bukti_pembayaran)
                                    <!-- Modal Lihat Bukti (Full Preview) -->
                                    <div class="modal fade" id="viewBuktiModal{{ $t->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Bukti Pembayaran</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-center">
                                                    <img src="{{ asset('storage/' . str_replace('\\', '/', $t->bukti_pembayaran)) }}" class="img-fluid rounded shadow-sm" alt="Bukti Pembayaran">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                @endif

                                @if($t->status != 'lunas')
                                    <!-- Modal Upload Ulang / Manual -->
                                    <div class="modal fade" id="uploadModal{{ $t->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form action="{{ route('penghuni.tagihan.upload_bukti', $t->id) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">{{ $t->bukti_pembayaran ? 'Upload Ulang Bukti Pembayaran' : 'Upload Bukti Pembayaran' }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-info small">
                                                            Setelah Anda melakukan pembayaran via Midtrans atau Transfer Manual, silakan upload bukti pembayaran (screenshot/struk) di sini agar admin dapat melakukan verifikasi.
                                                        </div>
                                                        @if($t->bukti_pembayaran)
                                                        <div class="mb-3">
                                                            <label class="form-label d-block">Bukti Saat Ini:</label>
                                                            <img src="{{ asset('storage/' . str_replace('\\', '/', $t->bukti_pembayaran)) }}" class="img-thumbnail" style="max-height: 150px;">
                                                        </div>
                                                        @endif
                                                        <div class="mb-3">
                                                            <label class="form-label">Pilih Foto Bukti Baru</label>
                                                            <input type="file" name="bukti_pembayaran" class="form-control" accept="image/*" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Upload</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif

                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-check-circle-fill text-success" style="font-size:24px;"></i><br>
                                Tidak ada tagihan aktif saat ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TABEL RIWAYAT PEMBAYARAN (LUNAS) --}}
        <div class="col-12 mt-3">
            <h6 class="fw-bold mb-3"><i class="bi bi-clock-history text-success"></i> Riwayat Pembayaran (Lunas)</h6>
            <div class="table-responsive bg-white rounded shadow-sm border">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Bulan / Keterangan</th>
                            <th>Tanggal Dibayar</th>
                            <th>Kamar</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $riwayatLunas = $tagihans->where('status', 'lunas');
                        @endphp
                        @forelse($riwayatLunas as $t)
                        <tr>
                            <td>
                                <div class="fw-bold d-flex align-items-center gap-2">
                                    {{ $t->bulan }}
                                    @if($t->keterangan && str_contains($t->keterangan, 'Denda kehilangan kartu'))
                                        <span class="badge bg-danger" style="font-size:10px;">
                                            <i class="bi bi-exclamation-triangle-fill"></i> Denda Kartu
                                        </span>
                                    @endif
                                </div>
                                @if($t->keterangan)
                                    <div class="text-muted small mt-1" style="max-width: 220px; white-space: normal;">
                                        <i class="bi bi-info-circle"></i> {{ $t->keterangan }}
                                    </div>
                                @endif
                            </td>
                            <td>{{ $t->tanggal_bayar ? \Carbon\Carbon::parse($t->tanggal_bayar)->format('d M Y') : \Carbon\Carbon::parse($t->updated_at)->format('d M Y') }}</td>
                            <td>Kamar {{ $t->kamar->nomor_kamar ?? '-' }}</td>
                            <td>Rp {{ number_format($t->jumlah_tagihan, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge bg-success"><i class="bi bi-check-all"></i> Lunas</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada riwayat pembayaran yang lunas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
