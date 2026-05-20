<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnggotaKeluarga extends Model
{
    protected $fillable = [
        'user_id',
        'penghuni_id',
        'nama',
        'hubungan',
        'telepon',
        'foto_ktp',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function penghuni()
    {
        return $this->belongsTo(Penghuni::class);
    }
}
