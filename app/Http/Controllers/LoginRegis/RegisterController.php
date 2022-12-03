<?php

namespace App\Http\Controllers\LoginRegis;

use App\Http\Controllers\Controller;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),[
            "users_nama" => "required",
            "users_email" => "required | email | unique:users,users_email",
            "users_password" => "required | confirmed",
            "users_alamat" => "required",
            "users_telepon" => "required | numeric | digits_between:8,12 | unique:users,users_telepon",
            "users_role" => [Rule::in(["customer","provider"])],
        ]);

        if ($validator->fails()) {
            return response() ->json([
                'status' => 'unprocessable content',
                'message' => 'The request is in the correct form, but the content is invalid. Check your api manual',
                'errors' => $validator->errors(),
            ],422);
        }

        // all good
        Users::create([
            "users_nama" => $request->users_nama,
            "users_email" => $request->users_email,
            "users_password" => Hash::make($request->users_password),
            "users_alamat" => $request->users_alamat,
            "users_telepon" => $request->users_telepon,
            "users_role" => $request->users_role,
            "users_status" => $request->users_role == "customer" ? "aktif" : "menunggu"
        ]);

        return response([
            "status" => "created",
            "message" => "successfully registered new account"
        ],201);

        // $name = $request->name;
        // $email = $request->email;
        // $password = $request->password;
        // $alamat = $request->alamat;
        // $telepon = $request->telepon;
        // $role = $request->role;

        // $request->validate([
        //     "name" => "required",
        //     "email" => "required | email | unique:users,users_email",
        //     "password" => "required | confirmed",
        //     "alamat" => "required",
        //     "telepon" => "required | numeric | digits_between:8,12 | unique:users,users_telepon",
        //     "tna" => "accepted",
        // ]);

        // if ($role == "customer") {
        //     $status = "aktif";
        // } else {
        //     $status = "menunggu";
        // }

        // $result = Users::create([
        //     "users_email" => $email,
        //     "users_telepon" => $telepon,
        //     "users_nama" => $name,
        //     "users_alamat" => $alamat,
        //     "users_password" => Hash::make($password),
        //     "users_role" => $role,
        //     "users_status" => $status,
        // ]);



        // if ($result) {
        //     return redirect()->route('view-login')->with("message", "Berhasil Register!");
        // }

        // return back()->with("message", "Gagal Register!");
    }
}
