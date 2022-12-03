<?php

namespace App\Http\Middleware;

use Closure;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;

class CheckApiRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if (auth()->check()) {
            if (auth()->user()->users_role == $role) {
                return $next($request);
            } else {
                return response()->json([
                    'status' => "forbidden",
                    "message" => "You are not authorized to use this resource"
                ],403);
            }
        } else {
            return response()->json([
                "status" => 'unauthorized',
                'message' => "you are not logged in"
            ],401);
        }
    }
}
