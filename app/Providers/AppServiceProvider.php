<?php

namespace App\Providers;

use Illuminate\Http\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Response::macro('dro', function ($status,$statuscode,$message,$data) {
            return Response::make([
                'status' => $status,
                'message' => $message,
                'data' => $data
            ], $statuscode);
        });

        Response::macro('caps', function ($value) {
            return Response::make(strtoupper($value));
        });
    }
}
