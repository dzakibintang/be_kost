<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kos;
use App\Models\KosFacility;
use Tymon\JWTAuth\Facades\JWTAuth;

class KosFacilityController extends Controller
{
    // List fasilitas kos
    public function index($kos_id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $kos = Kos::where('user_id', $user->id)->where('id', $kos_id)->firstOrFail();

        return response()->json($kos->facilities);
    }

    // Tambah fasilitas kos
    public function store(Request $request, $kos_id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $request->validate([
            'facility' => 'required|string|max:100'
        ]);

        $kos = Kos::where('user_id', $user->id)->where('id', $kos_id)->firstOrFail();

        $facility = KosFacility::create([
            'kos_id' => $kos->id,
            'facility' => $request->facility
        ]);

        return response()->json(['message' => 'Fasilitas berhasil ditambahkan', 'data' => $facility], 201);
    }

    // Update fasilitas kos
    public function update(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $facility = KosFacility::findOrFail($id);

        // cek kos milik user
        if ($facility->kos->user_id != $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'facility' => 'required|string|max:100'
        ]);

        $facility->update(['facility' => $request->facility]);

        return response()->json(['message' => 'Fasilitas berhasil diupdate', 'data' => $facility]);
    }

    // Hapus fasilitas kos
    public function destroy($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $facility = KosFacility::findOrFail($id);

        // cek kos milik user
        if ($facility->kos->user_id != $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $facility->delete();

        return response()->json(['message' => 'Fasilitas berhasil dihapus']);
    }
}
