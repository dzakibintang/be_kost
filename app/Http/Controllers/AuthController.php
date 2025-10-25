<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // 🔹 REGISTER (tidak mengembalikan token)
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name'     => 'required|string|max:100',
                'email'    => 'required|string|email|unique:users,email',
                'password' => 'required|string|min:6',
                'phone'    => 'nullable|string|max:20',
                'role'     => 'required|in:owner,society',
            ]);

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'phone'    => $request->phone,
                'role'     => $request->role,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Register berhasil',
                'user'    => $user
            ], 201);

        } catch (ValidationException $e) {
            // 🔸 Jika validasi gagal (termasuk email sudah digunakan)
            $errors = $e->errors();

            if (isset($errors['email'])) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Email sudah digunakan, silakan gunakan email lain.'
                ], 422);
            }

            return response()->json([
                'status'  => false,
                'message' => 'Data tidak valid',
                'errors'  => $errors
            ], 422);

        } catch (QueryException $e) {
            // 🔸 Antisipasi error database
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan pada server saat registrasi.'
            ], 500);
        }
    }

    // 🔹 LOGIN (token dikembalikan)
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // 🔍 cek apakah email terdaftar
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Email tidak ditemukan'
            ], 404);
        }

        // 🔍 cek apakah password benar
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Password salah'
            ], 401);
        }

        // ✅ generate token JWT
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status'  => true,
            'message' => 'Login berhasil',
            'user'    => $user,
            'token'   => $token,
            'role'    => $user->role
        ]);
    }

    // 🔹 LOGOUT (hapus token)
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'status'  => true,
                'message' => 'Logout berhasil'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal logout, token tidak valid'
            ], 500);
        }
    }
}
