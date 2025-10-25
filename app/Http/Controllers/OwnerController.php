<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class OwnerController extends Controller
{
    // 🔹 Tampilkan profil owner yang sedang login
    public function show()
    {
        try {
            $owner = JWTAuth::parseToken()->authenticate();

            return response()->json([
                'message' => 'Data profil owner',
                'data' => $owner
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil profil owner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 🔹 Update profil owner
    public function update(Request $request)
    {
        try {
            $owner = JWTAuth::parseToken()->authenticate();

            $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $owner->id,
                'phone' => 'nullable|string|max:20',
                'password' => 'nullable|min:6|confirmed'
            ]);

            $data = $request->only('name', 'email', 'phone');

            if ($request->filled('password')) {
                $data['password'] = bcrypt($request->password);
            }

            $owner->update($data);

            return response()->json([
                'message' => 'Profil owner berhasil diperbarui',
                'data' => $owner
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui profil owner',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
