<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KosFacility extends Model
{
    use HasFactory;

    protected $table = 'kos_facilities';

    protected $fillable = [
        'kos_id',
        'facility',
    ];

    public $timestamps = false;
    
    public function kos()
    {
        return $this->belongsTo(Kos::class, 'kos_id');
    }
}
