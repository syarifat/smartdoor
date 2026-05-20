<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'telepon',
        'alamat',
        'foto_ktp',
        'verification_token',
        'token_expires_at',
    ];

    public function penghuni()
    {
        return $this->hasOne(Penghuni::class);
    }

    public function anggotaKeluargas()
    {
        return $this->hasMany(AnggotaKeluarga::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }

    public function hasValidToken($token): bool
    {
        return $this->verification_token === $token && 
               $this->token_expires_at && 
               $this->token_expires_at > now();
    }
}
