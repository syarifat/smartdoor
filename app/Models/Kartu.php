<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kartu extends Model
{
    protected $table = 'kartu';

    protected $fillable = [
        'uid', 'penghuni_id', 'status'
    ];

    public function penghuni()
    {
        return $this->belongsTo(Penghuni::class);
    }
}
