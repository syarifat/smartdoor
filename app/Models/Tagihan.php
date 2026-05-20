<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    use HasFactory;

    protected $fillable = [
        'penghuni_id', 'kamar_id', 'bulan', 'jumlah_tagihan', 'status',
        'midtrans_order_id', 'midtrans_token', 'midtrans_url', 'midtrans_response',
        'tanggal_tagihan', 'tanggal_bayar', 'keterangan', 'bukti_pembayaran'
    ];

    public function penghuni()
    {
        return $this->belongsTo(Penghuni::class);
    }

    public function kamar()
    {
        return $this->belongsTo(Kamar::class);
    }
}
