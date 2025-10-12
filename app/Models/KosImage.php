<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KosImage extends Model
{
    use HasFactory;

    protected $table = 'kos_images';

    protected $fillable = [
        'kos_id',
        'file',
    ];

    public function kos()
    {
        return $this->belongsTo(Kos::class, 'kos_id');
    }
}
