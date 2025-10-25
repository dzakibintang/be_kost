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

    // 🔹 Relasi ke model Kos
    public function kos()
    {
        return $this->belongsTo(Kos::class, 'kos_id');
    }

    // 🔹 Tambahan biar frontend bisa langsung ambil URL lengkap
    protected $appends = ['url'];

    public function getUrlAttribute()
    {
        return asset($this->file);
    }
}
