<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;

    protected $table = 'facilities';
    protected $fillable = ['name'];

    public function kos()
    {
        return $this->belongsToMany(Kos::class, 'kos_facility', 'facility_id', 'kos_id');
    }
}
