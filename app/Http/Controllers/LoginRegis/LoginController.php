<?php

namespace App\Http\Controllers\LoginRegis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'users_email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return response() ->json([
                'status' => 'bad request',
                'message' => 'The request you made is incorrect, check your api manual',
                'validation_error' => $validator->errors(),
            ],400);
        }

        $credential = $validator->validated();

        $credential = $request->validate([
            'users_email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('web')->attempt($credential)) {
            session()->regenerate();
            $user = Auth::guard('web')->user();
            return response([
                'status' => 'success',
                'message' => 'successfuly logged in',
                'user' => [
                    'users_id' => $user->users_id,
                    'users_email' => $user->users_email,
                ],
            ],200);
        } else {
            return response([
                'status' => 'failed to login'
            ],200);
        }
    }

    public function logout(Request $request)
    {
        // $user = Auth::guard('web')->user();
        // return response()->json($request->user(),200);
        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();
        return response()->json([
            'status' => 'success',
            'message' => 'successfuly logged out',
        ],200);
    }
}
