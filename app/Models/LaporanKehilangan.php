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
    ];

    public function penghuni()
    {
        return $this->belongsTo(Penghuni::class);
    }

    public function kartu()
    {
        return $this->belongsTo(Kartu::class);
    }
}
