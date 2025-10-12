<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject; // 🔑 Tambahkan ini

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    // Nama tabel
    protected $table = 'users';

    // Kolom yang bisa diisi
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
    ];

    // Kolom yang harus disembunyikan
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relasi: 1 user bisa punya banyak kos
    public function kos()
    {
        return $this->hasMany(Kos::class, 'user_id');
    }

    // Relasi: 1 user bisa kasih banyak review
    public function reviews()
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    // Relasi: 1 user bisa booking banyak kos
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'user_id');
    }

    // 🔑 Tambahkan method ini untuk JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
