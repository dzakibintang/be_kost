<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\ReviewReply;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReviewReplyController extends Controller
{
    // ✅ Owner balas review
    public function store(Request $request)
    {
        $owner = JWTAuth::parseToken()->authenticate();

        $request->validate([
            'review_id' => 'required|exists:reviews,id',
            'reply' => 'required|string'
        ]);

        // Pastikan belum ada balasan sebelumnya
        if (ReviewReply::where('review_id', $request->review_id)->exists()) {
            return response()->json(['message' => 'Review ini sudah dibalas'], 409);
        }

        $reply = ReviewReply::create([
            'review_id' => $request->review_id,
            'owner_id' => $owner->id,
            'reply' => $request->reply
        ]);

        return response()->json([
            'message' => 'Balasan berhasil ditambahkan',
            'data' => $reply
        ], 201);
    }

    // ✅ Update balasan
    public function update(Request $request, $id)
    {
        $owner = JWTAuth::parseToken()->authenticate();

        $request->validate([
            'reply' => 'required|string'
        ]);

        $reply = ReviewReply::find($id);

        if (!$reply) {
            return response()->json(['message' => 'Balasan tidak ditemukan'], 404);
        }

        if ($reply->owner_id !== $owner->id) {
            return response()->json(['message' => 'Tidak diizinkan mengedit balasan ini'], 403);
        }

        $reply->update(['reply' => $request->reply]);

        return response()->json([
            'message' => 'Balasan berhasil diperbarui',
            'data' => $reply
        ]);
    }

    // ✅ Hapus balasan
    public function destroy($id)
    {
        $owner = JWTAuth::parseToken()->authenticate();

        $reply = ReviewReply::find($id);

        if (!$reply) {
            return response()->json(['message' => 'Balasan tidak ditemukan'], 404);
        }

        if ($reply->owner_id !== $owner->id) {
            return response()->json(['message' => 'Tidak diizinkan menghapus balasan ini'], 403);
        }

        $reply->delete();

        return response()->json(['message' => 'Balasan berhasil dihapus']);
    }
}
