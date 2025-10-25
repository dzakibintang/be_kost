<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';

    protected $fillable = [
        'kos_id',
        'user_id',
        'comment',
        'rating'
    ];

    public function kos()
    {
        return $this->belongsTo(Kos::class, 'kos_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 🔹 Relasi ke balasan dari owner (satu review hanya punya satu balasan)
    public function reply()
    {
        return $this->hasOne(ReviewReply::class, 'review_id');
    }
}
