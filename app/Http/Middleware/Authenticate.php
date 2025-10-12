<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // 🔑 Ubah ini: jangan arahkan ke 'login', tapi balikin JSON
        abort(response()->json([
            'status' => false,
            'message' => 'Token Salah, Silahkan login kembali.'
        ], 401));
    }
}
