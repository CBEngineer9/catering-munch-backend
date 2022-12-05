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
            'users_email' => ['required', 'email', 'exists:App\Models\Users,users_email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return response() ->json([
                'status' => 'unprocessable content',
                'message' => 'Wrong email / password',
                'errors' => $validator->errors(),
            ],422);
        }

        $credential = $validator->validated();

        if (Auth::guard('web')->attempt($credential)) {
            session()->regenerate();
            $user = Auth::guard('web')->user();
            return response()->json([
                'status' => 'success',
                'message' => 'successfuly logged in',
                'user' => [
                    'users_id' => $user->users_id,
                    'users_email' => $user->users_email,
                    'users_role' => $user->users_role,
                ],
            ],200);
        } else {
            return response([
                'status' => 'unprocessable request',
                'message' => 'Wrong email / password'
            ],422);
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
