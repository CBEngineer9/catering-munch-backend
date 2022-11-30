<?php

namespace App\Http\Controllers\LoginRegis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credential = $request->validate([
            'users_email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credential)) {
            $request->session()->regenerate();
            return response([
                'status' => 'success'
            ],200);
        } else {
            return response([
                'status' => 'failed to login'
            ],200);
        }
    }
}
