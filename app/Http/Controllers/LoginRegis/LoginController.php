<?php

namespace App\Http\Controllers\LoginRegis;

use App\Helpers\LogHelper;
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
            LogHelper::log("alert","failed login attempt","from " . $request->ip(). ", reason: bad request",1);

            return response() ->json([
                'status' => 'unprocessable content',
                'message' => 'Wrong email / password',
                'errors' => $validator->errors(),
            ],422);
        }

        $user = Users::where("users_email",$request->users_email)->first();
        if ($user->users_status === 'banned') {
            LogHelper::log("alert","failed login attempt","from " . $request->ip(). ", reason: banned",1);

            return response() ->json([
                'status' => 'bad request',
                'message' => 'you are banned',
            ],400);
        }

        $credential = $validator->validated();

        if (Auth::guard('web')->attempt($credential)) {
            session()->regenerate();
            $user = Auth::guard('web')->user();

            LogHelper::log("alert","successful login","from " . $request->ip(),$user->users_id);
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
            LogHelper::log("alert","failed login attempt","from " . $request->ip(). ", reason: wrong credentials",1);
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
            LogHelper::log("alert","failed login attempt","from " . $request->ip(). ", reason: bad request",1);
            return response() ->json([
                'status' => 'unprocessable content',
                'message' => 'Wrong email / password',
                'errors' => $validator->errors(),
            ],422);
        }

        $user = Users::where("users_email",$request->users_email)->first();
        if ($user->users_status === 'banned') {
            LogHelper::log("alert","failed login attempt","from " . $request->ip(). ", reason: banned",1);
            return response() ->json([
                'status' => 'bad request',
                'message' => 'you are banned',
            ],400);
        }
        
        

        $credential = $validator->validated();

        if (Auth::attempt($credential)) {
            session()->regenerate();
            $user = Users::where('users_email', $request->users_email)->firstOrFail();

            $tokens = $user->tokens;
            // already has token
            if (count($tokens) !== 0) {
                // delete token
                $user->tokens()->delete();
            }

            // create new token
            $token = $user->createToken('auth_token')->plainTextToken;
            LogHelper::log("alert","successful login","from " . $request->ip(),$user->users_id);
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
            LogHelper::log("alert","failed login attempt","from " . $request->ip(). ", reason: wrong credentials",1);
            return response()->json([
                'status' => 'unprocessable request',
                'message' => 'Wrong email / password'
            ],422);
        }
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();

        LogHelper::log("alert","successful logout","from " . $request->ip(),$user->users_id);
        
        return response()->json([
            'status' => 'success',
            'message' => 'successfuly logged out',
        ],200);
    }

    public function logoutApi(Request $request)
    {
        $authUser = Auth::user();
        $user = Users::where('users_email', $authUser->users_email)->firstOrFail();
        $user->tokens()->delete();

        LogHelper::log("info","successful logout","from " . $request->ip(),$user->users_id);
        
        return response()->json([
            'status' => 'success',
            'message' => 'successfuly logged out',
        ],200);
    }
}
