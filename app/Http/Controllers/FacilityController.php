<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    // Menampilkan semua fasilitas (untuk dropdown di FE)
    public function index()
    {
        return response()->json(Facility::all());
    }
}
