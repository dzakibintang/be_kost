<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kos;
use App\Models\KosImage;
use App\Models\KosFacility;
use Tymon\JWTAuth\Facades\JWTAuth;

class KosController extends Controller
{
    // List kos milik owner
    public function index()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $kos = Kos::where('user_id', $user->id)
            ->with(['images', 'facilities'])
            ->get();

        return response()->json($kos);
    }

    // Tambah kos
    public function store(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $request->validate([
            'name' => 'required|string|max:100',
            'address' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'gender' => 'required|in:male,female,all',
            'total_rooms' => 'required|integer|min:1',
            'available_rooms' => 'required|integer|min:0',
            'facilities' => 'array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $kos = Kos::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'address' => $request->address,
            'description' => $request->description,
            'price' => $request->price,
            'gender' => $request->gender,
            'total_rooms' => $request->total_rooms,
            'available_rooms' => $request->available_rooms,
        ]);

        // Simpan fasilitas
        if ($request->has('facilities')) {
            foreach ($request->facilities as $facility) {
                KosFacility::create([
                    'kos_id' => $kos->id,
                    'facility' => $facility
                ]);
            }
        }

        // Simpan gambar
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $filename = time().'_'.$file->getClientOriginalName();
                $file->move(public_path('uploads/kos'), $filename);

                KosImage::create([
                    'kos_id' => $kos->id,
                    'file' => 'uploads/kos/'.$filename
                ]);
            }
        }

        return response()->json(['message' => 'Kos berhasil ditambahkan', 'data' => $kos], 201);
    }

    // Detail kos
    public function show($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $kos = Kos::where('user_id', $user->id)
            ->where('id', $id)
            ->with(['images', 'facilities'])
            ->firstOrFail();

        return response()->json($kos);
    }

    // Update kos
    public function update(Request $request, $id)
{
    $user = JWTAuth::parseToken()->authenticate();

    $kos = Kos::where('user_id', $user->id)
        ->where('id', $id)
        ->firstOrFail();

    $kos->update($request->only([
        'name',
        'address',
        'description',
        'price',
        'gender',
        'total_rooms',
        'available_rooms'
    ]));

    return response()->json([
        'message' => 'Berhasil update',
        'data' => $kos
    ]);
}

    // Hapus kos
    public function destroy($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $kos = Kos::where('user_id', $user->id)->where('id', $id)->firstOrFail();
        $kos->delete();

        return response()->json(['message' => 'Kos berhasil dihapus']);
    }
}
