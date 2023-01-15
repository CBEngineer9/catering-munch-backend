<?php

namespace App\Http\Controllers\LoginRegis;

use App\Helpers\LogHelper;
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
            "users_role" => ["required", Rule::in(["customer","provider"])],
            "users_desc" => [Rule::requiredIf($request->users_role === "provider"), "nullable", "string"],
            "tnc" => "accepted",
        ]);

        if ($validator->fails()) {
            LogHelper::log("alert","failed register attempt","from " . $request->ip(). ", reason: validation fail".$validator->errors(),1);
            
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
            'users_desc' => $request->users_desc,
            "users_role" => $request->users_role,
            "users_status" => $request->users_role == "customer" ? "aktif" : "menunggu"
        ]);

        LogHelper::log("info","successful register","from " . $request->ip(),1);

        return response()->json([
            "status" => "created",
            "message" => "successfully registered new account"
        ],201);
    }
}
