<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAkses extends Model
{
    protected $table = 'log_akses';
    public $timestamps = false;

    protected $fillable = [
        'uid', 'penghuni_id', 'kamar_id', 'status', 'aksi', 'keterangan', 'waktu', 'metode_akses'
    ];

    protected $casts = [
        'waktu' => 'datetime',
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
