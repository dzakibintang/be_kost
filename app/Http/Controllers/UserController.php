<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'status' => true,
            'message' => 'Welcome to Society Dashboard',
            'user' => auth()->user()
        ]);
    }
}
