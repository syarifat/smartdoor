<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penghuni extends Model
{
    protected $table = 'penghunis';

    protected $fillable = [
        'user_id',
        'kamar_id',
        'nama',
        'telepon',
        'alamat',
        'foto_ktp',
        'pin',
        'pin_aktif',
    ];

    protected $casts = [
        'pin_aktif' => 'boolean',
    ];

    public function verifikasiPin($inputPin)
    {
        return \Illuminate\Support\Facades\Hash::check($inputPin, $this->pin);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function anggotaKeluargas()
    {
        return $this->hasMany(AnggotaKeluarga::class);
    }

    public function kamar()
    {
        return $this->belongsTo(Kamar::class, 'kamar_id');
    }
}