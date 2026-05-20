@extends('layouts.app')

@section('page-title', 'Pembayaran Tagihan')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card-table text-center p-4">
            <div style="font-size: 40px; color: #2d6a9f; margin-bottom: 15px;">
                <i class="bi bi-wallet2"></i>
            </div>
            <h4 class="fw-bold mb-1">Rp {{ number_format($tagihan->jumlah_tagihan, 0, ',', '.') }}</h4>
            <p class="text-muted mb-4">Tagihan Bulan {{ $tagihan->bulan }}</p>

            <table class="table table-sm text-start mb-4">
                <tr>
                    <td class="text-muted border-0">Nama Penghuni</td>
                    <td class="fw-bold text-end border-0">{{ $tagihan->penghuni->nama }}</td>
                </tr>
                <tr>
                    <td class="text-muted border-0">Kamar</td>
                    <td class="fw-bold text-end border-0">Kamar {{ $tagihan->kamar->nomor_kamar ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="text-muted border-0">Tanggal Tagihan</td>
                    <td class="fw-bold text-end border-0">{{ \Carbon\Carbon::parse($tagihan->tanggal_tagihan)->format('d M Y') }}</td>
                </tr>
            </table>

            <button id="pay-button" class="btn btn-primary w-100 py-2 fw-bold" style="background:#2d6a9f; border:none; border-radius:10px;">
                Lanjutkan Pembayaran <i class="bi bi-arrow-right ms-1"></i>
            </button>
            <div class="mt-3">
                <a href="{{ route('penghuni.tagihan.index') }}" class="text-decoration-none text-muted" style="font-size:14px;">Kembali</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Midtrans Snap JS dimuat SETELAH semua script layout (Bootstrap, SweetAlert) --}}
<script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
        data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
    const snapToken    = @json($snapToken);
    const tagihanId    = @json($tagihan->id);
    const checkUrl     = "{{ route('penghuni.tagihan.check_status', $tagihan->id) }}";
    const csrfToken    = "{{ csrf_token() }}";
    const redirectUrl  = "{{ route('penghuni.tagihan.index') }}";

    /**
     * Verifikasi status pembayaran ke backend, lalu redirect.
     * Pendekatan ini mengatasi masalah webhook Midtrans tidak bisa
     * menjangkau localhost (development environment).
     */
    function verifyAndRedirect(resultData, isSuccess) {
        // Tampilkan loading
        Swal.fire({
            title: 'Memverifikasi Pembayaran...',
            text: 'Mohon tunggu sebentar.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => { Swal.showLoading(); }
        });

        fetch(checkUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ result: resultData })
        })
        .then(res => res.json())
        .then(data => {
            if (isSuccess && data.success) {
                let countdown = 3;
                Swal.fire({
                    icon: 'success',
                    title: 'Pembayaran Berhasil!',
                    html: `Status tagihan telah diperbarui.<br><br>Halaman akan dialihkan dalam <b>${countdown}</b> detik...`,
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#2d6a9f',
                    timer: countdown * 1000,
                    timerProgressBar: true,
                    didOpen: () => {
                        const interval = setInterval(() => {
                            countdown--;
                            const html = Swal.getHtmlContainer();
                            if (html) {
                                const b = html.querySelector('b');
                                if (b) b.textContent = countdown;
                            }
                            if (countdown <= 0) clearInterval(interval);
                        }, 1000);
                    }
                }).then(() => {
                    window.location.href = redirectUrl;
                });
            } else {
                // Tetap redirect meski verifikasi gagal (bisa dicek manual oleh admin)
                window.location.href = redirectUrl;
            }
        })
        .catch(() => {
            // Jika fetch gagal, tetap redirect
            window.location.href = redirectUrl;
        });
    }

    document.getElementById('pay-button').addEventListener('click', function () {
        if (typeof snap === 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Memuat Midtrans',
                text: 'Script pembayaran tidak berhasil dimuat. Pastikan koneksi internet aktif dan coba refresh halaman.',
                confirmButtonColor: '#2d6a9f'
            });
            return;
        }

        snap.pay(snapToken, {
            onSuccess: function(result) {
                // Verifikasi ke backend sebelum redirect
                verifyAndRedirect(result, true);
            },
            onPending: function(result) {
                // Update status ke pending dulu
                verifyAndRedirect(result, false);
            },
            onError: function(result) {
                Swal.fire({
                    icon: 'error',
                    title: 'Pembayaran Gagal',
                    text: 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.',
                    confirmButtonColor: '#2d6a9f'
                });
            },
            onClose: function() {
                Swal.fire({
                    icon: 'info',
                    title: 'Pembayaran Dibatalkan',
                    text: 'Kamu menutup popup sebelum menyelesaikan pembayaran.',
                    confirmButtonColor: '#2d6a9f'
                });
            }
        });
    });
</script>
@endpush
