<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PercobaanGagal extends Model
{
    protected $table = 'percobaan_gagals';

    protected $fillable = [
        'kamar_id',
        'rfid_uid',
        'jumlah_percobaan',
        'foto_path',
        'sudah_dilihat',
        'waktu',
    ];

    protected $casts = [
        'waktu'         => 'datetime',
        'sudah_dilihat' => 'boolean',
    ];

    public function kamar()
    {
        return $this->belongsTo(Kamar::class);
    }
}
