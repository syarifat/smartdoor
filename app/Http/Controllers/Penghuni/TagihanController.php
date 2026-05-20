<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tagihan;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class TagihanController extends Controller
{
    public function index()
    {
        $penghuni = Auth::user()->penghuni;
        $tagihans = [];
        if ($penghuni) {
            $tagihans = Tagihan::where('penghuni_id', $penghuni->id)->orderBy('tanggal_tagihan', 'desc')->get();
        }
        return view('penghuni.tagihan.index', compact('tagihans'));
    }

    /**
     * GET /tagihan/{id}/bayar
     * Tampilkan halaman pembayaran dengan Snap Token baru.
     * Memungkinkan akses via browser history tanpa error 405.
     */
    public function showBayar($id)
    {
        $penghuni = Auth::user()->penghuni;
        $tagihan = Tagihan::where('id', $id)->where('penghuni_id', $penghuni->id)->firstOrFail();

        if ($tagihan->status == 'lunas') {
            return redirect()->route('penghuni.tagihan.index')->with('success', 'Tagihan ini sudah lunas.');
        }

        // Setup Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        $orderId = 'TAGIHAN-' . $tagihan->id . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $tagihan->jumlah_tagihan,
            ],
            'customer_details' => [
                'first_name' => $penghuni->nama,
                'email'      => Auth::user()->email,
                'phone'      => $penghuni->telepon,
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            $tagihan->update([
                'midtrans_order_id' => $orderId,
                'midtrans_token'    => $snapToken,
            ]);

            return view('penghuni.tagihan.bayar', compact('tagihan', 'snapToken'));

        } catch (\Exception $e) {
            return redirect()->route('penghuni.tagihan.index')
                ->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * POST /tagihan/{id}/bayar
     * Redirect ke halaman GET bayar (dipakai oleh tombol di halaman index).
     */
    public function bayar($id)
    {
        return redirect()->route('penghuni.tagihan.show_bayar', $id);
    }

    public function uploadBukti(Request $request, $id)
    {
        $request->validate([
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $penghuni = Auth::user()->penghuni;
        $tagihan = Tagihan::where('id', $id)->where('penghuni_id', $penghuni->id)->firstOrFail();

        if ($request->hasFile('bukti_pembayaran')) {
            if ($tagihan->bukti_pembayaran) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($tagihan->bukti_pembayaran);
            }
            
            $path = $request->file('bukti_pembayaran')->store('bukti_pembayaran', 'public');
            $tagihan->update([
                'bukti_pembayaran' => $path,
                'status' => 'menunggu_verifikasi'
            ]);
            
            return redirect()->route('penghuni.tagihan.index')->with('success', 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.');
        }
        
        return redirect()->back()->with('error', 'Gagal mengunggah file.');
    }

    public function notification(Request $request)
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');

        $payload = $request->getContent();
        $notification = json_decode($payload);

        $validSignatureKey = hash("sha512", $notification->order_id . $notification->status_code . $notification->gross_amount . Config::$serverKey);
        
        if ($notification->signature_key != $validSignatureKey) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $transaction = $notification->transaction_status;
        $type = $notification->payment_type;
        $orderId = $notification->order_id;
        $fraud = $notification->fraud_status;

        $tagihan = Tagihan::where('midtrans_order_id', $orderId)->first();

        if (!$tagihan) {
            return response()->json(['message' => 'Tagihan not found'], 404);
        }

        if ($transaction == 'capture') {
            if ($type == 'credit_card') {
                if($fraud == 'challenge'){
                    $tagihan->update(['status' => 'menunggu_verifikasi']);
                } else {
                    $tagihan->update(['status' => 'menunggu_verifikasi', 'midtrans_response' => $payload]);
                }
            }
        } else if ($transaction == 'settlement') {
            $tagihan->update(['status' => 'menunggu_verifikasi', 'midtrans_response' => $payload]);
        } else if ($transaction == 'pending') {
            $tagihan->update(['status' => 'menunggu_verifikasi']);
        } else if ($transaction == 'deny' || $transaction == 'expire' || $transaction == 'cancel') {
            $tagihan->update(['status' => 'belum_bayar']);
        }

        return response()->json(['message' => 'Notification processed successfully']);
    }

    /**
     * POST /tagihan/{id}/check-status
     * Dipanggil oleh frontend (onSuccess Snap) untuk verifikasi status
     * transaksi langsung ke Midtrans API dan update database.
     * Ini mengatasi masalah webhook Midtrans tidak bisa menjangkau localhost.
     */
    public function checkStatus(Request $request, $id)
    {
        $penghuni = Auth::user()->penghuni;
        $tagihan  = Tagihan::where('id', $id)
            ->where('penghuni_id', $penghuni->id)
            ->firstOrFail();

        if (!$tagihan->midtrans_order_id) {
            return response()->json(['success' => false, 'message' => 'Order ID tidak ditemukan'], 400);
        }

        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');

        try {
            // Query langsung ke Midtrans API
            $status = Transaction::status($tagihan->midtrans_order_id);

            $transactionStatus = $status->transaction_status ?? null;
            $fraudStatus       = $status->fraud_status ?? null;
            $paymentType       = $status->payment_type ?? null;

            $updated = false;

            if ($transactionStatus === 'capture') {
                if ($fraudStatus === 'challenge') {
                    $tagihan->update(['status' => 'menunggu_verifikasi']);
                    $updated = true;
                } elseif ($fraudStatus === 'accept') {
                    $tagihan->update(['status' => 'menunggu_verifikasi', 'midtrans_response' => json_encode($status)]);
                    $updated = true;
                }
            } elseif ($transactionStatus === 'settlement') {
                $tagihan->update(['status' => 'menunggu_verifikasi', 'midtrans_response' => json_encode($status)]);
                $updated = true;
            } elseif ($transactionStatus === 'pending') {
                $tagihan->update(['status' => 'menunggu_verifikasi']);
                $updated = true;
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $tagihan->update(['status' => 'belum_bayar']);
                $updated = true;
            }

            return response()->json([
                'success'            => true,
                'transaction_status' => $transactionStatus,
                'tagihan_status'     => $tagihan->fresh()->status,
                'updated'            => $updated,
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Midtrans checkStatus error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

}
