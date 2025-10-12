<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'status' => true,
            'message' => 'Welcome to Owner/Admin Dashboard',
            'user' => auth()->user()
        ]);
    }
}
