<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kos;

class UserController extends Controller
{
    // 🔹 Dashboard user (society)
    public function dashboard()
    {
        return response()->json([
            'status' => true,
            'message' => 'Welcome to Society Dashboard',
            'user' => auth()->user()
        ]);
    }

    // 🔹 Lihat semua kos dengan filter gender, harga, dan ketersediaan kamar
    public function listKos(Request $request)
    {
        $query = Kos::with(['images', 'facilities', 'user:id,name,email']);

        // Filter berdasarkan gender (male / female / all)
        if ($request->has('gender')) {
            $query->where('gender', $request->gender);
        }

        // Filter harga minimum
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        // Filter harga maksimum
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter hanya kos yang masih tersedia
if ($request->has('available_only') && $request->available_only == 'true') {
    $query->where('available_rooms', '>', 0);
}


        $kos = $query->get();

        return response()->json([
            'status' => true,
            'message' => 'Daftar semua kos',
            'total' => $kos->count(),
            'data' => $kos
        ]);
    }

    // 🔹 Lihat detail satu kos berdasarkan ID
    public function showKos($id)
    {
        $kos = Kos::with(['images', 'facilities', 'user:id,name,email'])
            ->find($id);

        if (!$kos) {
            return response()->json(['message' => 'Kos tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Detail kos',
            'data' => $kos
        ]);
    }
}
