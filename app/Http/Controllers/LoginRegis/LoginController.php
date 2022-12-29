<?php

namespace App\Http\Controllers\LoginRegis;

use App\Http\Controllers\Controller;
use App\Models\Users;
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

        $user = Users::where("users_email",$request->users_email)->first();
        if ($user->users_status === 'banned') {
            return response() ->json([
                'status' => 'bad request',
                'message' => 'you are banned',
            ],400);
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
            return response()->json([
                'status' => 'unprocessable request',
                'message' => 'Wrong email / password'
            ],422);
        }
    }

    public function loginApi(Request $request)
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

        $user = Users::where("users_email",$request->users_email)->first();
        if ($user->users_status === 'banned') {
            return response() ->json([
                'status' => 'bad request',
                'message' => 'you are banned',
            ],400);
        }
        
        // already has token
        if ($user->currentAccessToken() !== null) {
            return response()->json([
                'status' => 'success',
                'message' => 'already logged in',
                'data' => [
                    'users_id' => $user->users_id,
                    'users_email' => $user->users_email,
                    'users_role' => $user->users_role,
                    'access_token' => $user->currentAccessToken(),
                    'token_type' => 'Bearer'
                ],
            ],200);
        }

        $credential = $validator->validated();

        if (Auth::attempt($credential)) {
            session()->regenerate();
            $user = Users::where('users_email', $request->users_email)->firstOrFail();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'successfuly logged in',
                'data' => [
                    'users_id' => $user->users_id,
                    'users_email' => $user->users_email,
                    'users_role' => $user->users_role,
                    'access_token' => $token,
                    'token_type' => 'Bearer'
                ],
            ],200);
        } else {
            return response()->json([
                'status' => 'unprocessable request',
                'message' => 'Wrong email / password'
            ],422);
        }
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();
        return response()->json([
            'status' => 'success',
            'message' => 'successfuly logged out',
        ],200);
    }

    public function logoutApi()
    {
        $authUser = Auth::user();
        $user = Users::where('users_email', $authUser->users_email)->firstOrFail();
        $user->tokens()->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'successfuly logged out',
        ],200);
    }
}
