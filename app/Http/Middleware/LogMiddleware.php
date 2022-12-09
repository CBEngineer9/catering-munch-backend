<?php

namespace App\Http\Middleware;

use App\Helpers\LogHelper;
use App\Models\HistoryLog;
use Closure;
use Illuminate\Http\Request;

class LogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $resp = $next($request);
        
        $title = "request served";
        $desc = "url:" . $request->fullUrl() . ", ip=" . $request->ip(). " status code=" . $resp->status();   
        LogHelper::log('info', $title, $desc, $request->user());
        return $resp;
    }
}
