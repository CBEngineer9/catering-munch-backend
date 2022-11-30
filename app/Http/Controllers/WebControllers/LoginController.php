<?php

namespace App\Http\Controllers\WebControllers;

use App\Http\Controllers\Controller;
use App\Models\Users;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->email;
        $password = $request->password;

        $request->validate([
            "email" => "required",
            "password" => "required",
        ]);

        $credentials = [
            "users_email" => $email,
            "password" => $password,
        ];

        if (auth()->attempt($credentials)) {
            if (auth()->user()->users_role == "admin") {
                return redirect()->route("view-admin");
            } else if (auth()->user()->users_role == "customer") {
                return redirect()->route('view-customer');
            } else {
                return redirect()->route('view-provider');
            }
        } else {
            return back()->with('message', "Gagal Login!");
        }
    }
}
