<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kos;
use App\Models\KosImage;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage; // ✅ Tambahan penting

class KosController extends Controller
{
    // ✅ List kos milik owner
    public function index()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $kos = Kos::where('user_id', $user->id)
            ->with(['images', 'facilities'])
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Daftar kos milik owner',
            'data' => $kos
        ]);
    }

    // ✅ Tambah kos
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
            'facilities' => 'nullable|array',
            'facilities.*' => 'integer|exists:facilities,id',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        DB::beginTransaction();

        try {
            // Buat data kos baru
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

            // Simpan fasilitas (many-to-many pivot)
            if ($request->has('facilities')) {
                $kos->facilities()->attach($request->facilities);
            }

            // 🔹 Simpan gambar (multi-upload)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    // ✅ Simpan file ke storage/app/public/uploads/kos
                    $path = $file->store('uploads/kos', 'public');

                    KosImage::create([
                        'kos_id' => $kos->id,
                        'file' => 'storage/' . $path // ✅ simpan path yang bisa diakses URL
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Kos berhasil ditambahkan',
                'data' => $kos->load(['facilities', 'images'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menambahkan kos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ✅ Detail kos
    public function show($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $kos = Kos::with(['images', 'facilities'])
            ->where('user_id', $user->id)
            ->find($id);

        if (!$kos) {
            return response()->json(['message' => 'Kos tidak ditemukan'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail kos',
            'data' => $kos
        ]);
    }

    // ✅ Update kos
    public function update(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $kos = Kos::where('user_id', $user->id)->find($id);

        if (!$kos) {
            return response()->json(['message' => 'Kos tidak ditemukan'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'address' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'gender' => 'required|in:male,female,all',
            'total_rooms' => 'required|integer|min:1',
            'available_rooms' => 'required|integer|min:0',
            'facilities' => 'nullable|array',
            'facilities.*' => 'integer|exists:facilities,id',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        DB::beginTransaction();

        try {
            $kos->update($request->only([
                'name', 'address', 'description', 'price',
                'gender', 'total_rooms', 'available_rooms'
            ]));

            // Sinkronkan fasilitas pivot
            if ($request->has('facilities')) {
                $kos->facilities()->sync($request->facilities);
            }

            // 🔹 Tambah gambar baru saat update (opsional)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $path = $file->store('uploads/kos', 'public');

                    KosImage::create([
                        'kos_id' => $kos->id,
                        'file' => 'storage/' . $path
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil update kos',
                'data' => $kos->load(['facilities', 'images'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal update kos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ✅ Hapus kos + gambar + fasilitas
    public function destroy($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $kos = Kos::with(['images', 'facilities'])->find($id);

        if (!$kos) {
            return response()->json(['message' => 'Kos tidak ditemukan'], 404);
        }

        if ($kos->user_id !== $user->id) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

        DB::beginTransaction();

        try {
            // Hapus file gambar dari storage
            foreach ($kos->images as $image) {
                $path = str_replace('storage/', '', $image->file);
                Storage::disk('public')->delete($path); // ✅ gunakan Storage Laravel
                $image->delete();
            }

            // Lepas pivot fasilitas
            $kos->facilities()->detach();

            // Hapus kos
            $kos->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Kos dan semua datanya berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus kos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ✅ Hapus satu gambar tertentu
    public function deleteImage($imageId)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $image = KosImage::with('kos')->find($imageId);

        if (!$image) {
            return response()->json(['status' => false, 'message' => 'Gambar tidak ditemukan'], 404);
        }

        if ($image->kos->user_id !== $user->id) {
            return response()->json(['status' => false, 'message' => 'Tidak diizinkan'], 403);
        }

        // ✅ Hapus dari storage
        $path = str_replace('storage/', '', $image->file);
        Storage::disk('public')->delete($path);

        $image->delete();

        return response()->json([
            'status' => true,
            'message' => 'Gambar berhasil dihapus'
        ]);
    }
}
