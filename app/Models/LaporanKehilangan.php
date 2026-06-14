<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanKehilangan extends Model
{
    protected $table = 'laporan_kehilangans';

    protected $fillable = [
        'penghuni_id',
        'kartu_id',
        'keterangan',
        'status',
        'jumlah_denda',
        'denda_ditagihkan',
    ];

    public function penghuni()
    {
        return $this->belongsTo(Penghuni::class);
    }

    public function isDendaLunas()
    {
        if (!$this->denda_ditagihkan) return false;
        
        $tagihan = \App\Models\Tagihan::where('penghuni_id', $this->penghuni_id)
            ->where('keterangan', 'LIKE', 'Denda kehilangan kartu akses RFID%')
            ->latest()
            ->first();
            
        return $tagihan && $tagihan->status === 'lunas';
    }

    public function kartu()
    {
        return $this->belongsTo(Kartu::class);
    }
}
