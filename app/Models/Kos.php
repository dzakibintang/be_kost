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

    /**
     * 🔹 Relasi: Kos dimiliki oleh satu User (owner)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 🔹 Relasi: Kos punya banyak gambar
     */
    public function images()
    {
        return $this->hasMany(KosImage::class, 'kos_id');
    }

    /**
     * 🔹 Relasi: Kos punya banyak fasilitas (many-to-many)
     * lewat tabel pivot kos_facility
     */
    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'kos_facility', 'kos_id', 'facility_id');
    }

    /**
     * 🔹 Relasi: Kos punya banyak review
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'kos_id');
    }

    /**
     * 🔹 Relasi: Kos punya banyak booking
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'kos_id');
    }
}
