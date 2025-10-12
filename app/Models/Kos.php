<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kos extends Model
{
    use HasFactory;

    protected $table = 'kos';

    protected $fillable = [
        'user_id',
        'name',
        'address',
        'description',
        'price',
        'gender',
        'total_rooms',
        'available_rooms',
    ];

    // Relasi: Kos dimiliki oleh 1 User (owner)
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi: Kos punya banyak gambar
    public function images()
    {
        return $this->hasMany(KosImage::class, 'kos_id');
    }

    // Relasi: Kos punya banyak fasilitas
    public function facilities()
    {
        return $this->hasMany(KosFacility::class, 'kos_id');
    }

    // Relasi: Kos punya banyak review
    public function reviews()
    {
        return $this->hasMany(Review::class, 'kos_id');
    }

    // Relasi: Kos punya banyak booking
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'kos_id');
    }
}
