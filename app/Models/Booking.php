<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    protected $fillable = [
        'kos_id',
        'user_id',
        'start_date',
        'end_date',
        'status',
        'approved_at',
    ];

    public function kos()
    {
        return $this->belongsTo(Kos::class, 'kos_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
