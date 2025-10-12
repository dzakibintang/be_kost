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
    ];

    public function kos()
    {
        return $this->belongsTo(Kos::class, 'kos_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function replies()
    {
        return $this->hasMany(ReviewReply::class, 'review_id');
    }
}
