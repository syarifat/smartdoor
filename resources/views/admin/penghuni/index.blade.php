@extends('layouts.app')

@section('page-title', 'Data Penghuni')

@section('content')


<div class="card-table">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="card-header-title">
            <i class="bi bi-people"></i> Daftar Penghuni
        </div>
        <a href="{{ route('admin.penghuni.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Tambah Penghuni
        </a>
    </div>

    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Nama</th>
                <th>Telepon</th>
                <th>Kamar</th>
                <th>Alamat</th>
                <th>Foto KTP</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($penghunis as $i => $penghuni)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    <div class="fw-semibold text-dark">{{ $penghuni->nama }}</div>
                    @if($penghuni->anggotaKeluargas && $penghuni->anggotaKeluargas->count() > 0)
                        <div style="font-size: 11px;" class="text-primary mt-1">
                            <i class="bi bi-people-fill"></i> +{{ $penghuni->anggotaKeluargas->count() }} Anggota
                        </div>
                    @endif
                </td>
                <td>{{ $penghuni->telepon ?? '-' }}</td>
                <td>
                    @if($penghuni->kamar)
                        <span class="badge bg-primary">
                            Kamar {{ $penghuni->kamar->nomor_kamar }}
                        </span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>{{ Str::limit($penghuni->alamat, 30) ?? '-' }}</td>
                <td>
                    @if($penghuni->foto_ktp)
                        <img src="{{ asset('storage/' . $penghuni->foto_ktp) }}" alt="KTP" 
                             style="height: 40px; border-radius: 4px; cursor: pointer; border: 1px solid #ddd;" 
                             onclick="showKtpModal(this.src)" title="Klik untuk perbesar">
                    @else
                        <span class="text-muted text-sm">Tidak ada</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.penghuni.edit', $penghuni) }}"
                       class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <form action="{{ route('admin.penghuni.destroy', $penghuni) }}"
                          method="POST" class="d-inline"
                          onsubmit="confirmSubmit(event, this, 'Yakin hapus penghuni ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    <i class="bi bi-inbox" style="font-size:30px"></i>
                    <br>Belum ada data penghuni
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modal Preview KTP -->
<div class="modal fade" id="ktpModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-card-heading"></i> Preview KTP</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center p-4">
        <img id="ktpImagePreview" src="" alt="KTP Error" class="img-fluid rounded shadow" style="max-height: 700px; width: 100%; object-fit: contain;">
      </div>
    </div>
  </div>
</div>

<style>
/* Efek background blur khusus untuk Modal */
.modal-backdrop.show {
    backdrop-filter: blur(8px);
    background-color: rgba(0, 0, 0, 0.6);
}
</style>

<script>


function showKtpModal(src) {
    document.getElementById('ktpImagePreview').src = src;
    var ktpModal = new bootstrap.Modal(document.getElementById('ktpModal'));
    ktpModal.show();
}
</script>

@endsection