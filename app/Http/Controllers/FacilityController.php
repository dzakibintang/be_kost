<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    // ✅ Ambil semua fasilitas (untuk dropdown di FE)
    public function index()
    {
        $facilities = Facility::all();

        return response()->json([
            'status' => true,
            'message' => 'Daftar semua fasilitas',
            'data' => $facilities
        ]);
    }

    // ✅ Tambah fasilitas baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100'
        ]);

        $facility = Facility::create([
            'name' => $request->name
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Fasilitas berhasil ditambahkan',
            'data' => $facility
        ], 201);
    }

    // ✅ Update fasilitas (optional)
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100'
        ]);

        $facility = Facility::find($id);

        if (!$facility) {
            return response()->json([
                'status' => false,
                'message' => 'Fasilitas tidak ditemukan'
            ], 404);
        }

        $facility->update(['name' => $request->name]);

        return response()->json([
            'status' => true,
            'message' => 'Fasilitas berhasil diperbarui',
            'data' => $facility
        ]);
    }

    // ✅ Hapus fasilitas (optional)
    public function destroy($id)
    {
        $facility = Facility::find($id);

        if (!$facility) {
            return response()->json([
                'status' => false,
                'message' => 'Fasilitas tidak ditemukan'
            ], 404);
        }

        $facility->delete();

        return response()->json([
            'status' => true,
            'message' => 'Fasilitas berhasil dihapus'
        ]);
    }
}
