<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kamar extends Model
{
    protected $table = 'kamars';

    protected $fillable = [
        'nomor_kamar',
        'status',
        'status_pintu',
        'perintah',
        'terakhir_diakses',
    ];

    protected $casts = [
        'terakhir_diakses' => 'datetime',
    ];

    public function penghuni()
    {
        return $this->hasMany(Penghuni::class);
    }
}