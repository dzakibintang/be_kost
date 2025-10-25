<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Kos;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReviewController extends Controller
{
    // ✅ Tambah review baru
    public function store(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $request->validate([
            'kos_id' => 'required|exists:kos,id',
            'comment' => 'required|string',
            'rating' => 'nullable|integer|min:1|max:5'
        ]);

        $review = Review::create([
            'kos_id' => $request->kos_id,
            'user_id' => $user->id,
            'comment' => $request->comment,
            'rating' => $request->rating,
        ]);

        // 🔄 Update rating kos setelah review baru ditambah
        $this->updateKosRating($request->kos_id);

        return response()->json([
            'message' => 'Review berhasil ditambahkan',
            'data' => $review->load('user:id,name')
        ], 201);
    }

    // ✅ Lihat semua review untuk 1 kos
    public function listByKos($kos_id)
    {
        $reviews = Review::with('user:id,name')
            ->where('kos_id', $kos_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Daftar review untuk kos ini',
            'total' => $reviews->count(),
            'data' => $reviews
        ]);
    }

    // ✅ Update review milik user sendiri
    public function update(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Review tidak ditemukan'], 404);
        }

        if ($review->user_id !== $user->id) {
            return response()->json(['message' => 'Tidak diizinkan mengubah review ini'], 403);
        }

        $request->validate([
            'comment' => 'nullable|string',
            'rating' => 'nullable|integer|min:1|max:5'
        ]);

        // Update hanya kolom yang dikirim
        $updateData = [];
        if ($request->has('comment')) $updateData['comment'] = $request->comment;
        if ($request->has('rating')) $updateData['rating'] = $request->rating;

        $review->update($updateData);

        // 🔄 Update rating kos setelah review diubah
        $this->updateKosRating($review->kos_id);

        return response()->json([
            'message' => 'Review berhasil diperbarui',
            'data' => $review->load('user:id,name')
        ]);
    }

    // ✅ Hapus review milik user sendiri
    public function destroy($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Review tidak ditemukan'], 404);
        }

        if ($review->user_id !== $user->id) {
            return response()->json(['message' => 'Tidak diizinkan menghapus review ini'], 403);
        }

        $kosId = $review->kos_id;
        $review->delete();

        // 🔄 Update rating kos setelah review dihapus
        $this->updateKosRating($kosId);

        return response()->json(['message' => 'Review berhasil dihapus']);
    }

    // 🔧 Fungsi bantu untuk update rata-rata rating kos
    private function updateKosRating($kos_id)
    {
        $kos = Kos::find($kos_id);
        if (!$kos) return;

        $averageRating = Review::where('kos_id', $kos_id)->avg('rating') ?? 0;

        $kos->update(['rating' => round($averageRating, 1)]);
    }
}
