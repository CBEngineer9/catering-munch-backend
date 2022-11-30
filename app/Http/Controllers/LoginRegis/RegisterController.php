<?php

namespace App\Http\Controllers\LoginRegis;

use App\Http\Controllers\Controller;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $name = $request->name;
        $email = $request->email;
        $password = $request->password;
        $alamat = $request->alamat;
        $telepon = $request->telepon;
        $role = $request->role;

        $request->validate([
            "name" => "required",
            "email" => "required | email | unique:users,users_email",
            "password" => "required | confirmed",
            "alamat" => "required",
            "telepon" => "required | numeric | digits_between:8,12 | unique:users,users_telepon",
            "tna" => "accepted",
        ]);

        if ($role == "customer") {
            $status = "aktif";
        } else {
            $status = "menunggu";
        }

        $result = Users::create([
            "users_email" => $email,
            "users_telepon" => $telepon,
            "users_nama" => $name,
            "users_alamat" => $alamat,
            "users_password" => Hash::make($password),
            "users_role" => $role,
            "users_status" => $status,
        ]);



        if ($result) {
            return redirect()->route('view-login')->with("message", "Berhasil Register!");
        }

        return back()->with("message", "Gagal Register!");
    }
}
