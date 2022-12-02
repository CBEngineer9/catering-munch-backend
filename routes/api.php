<?php

use App\Http\Controllers\Admin\HistoryController;
use App\Http\Controllers\LoginRegis\LoginController;
use App\Http\Controllers\ResourceControllers\MenuController;
use App\Http\Controllers\ResourceControllers\PesananController;
use App\Http\Controllers\ResourceControllers\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::middleware('auth:sanctum')->post('/logout', [LoginController::class, 'logout']);

Route::prefix('admin')->group(function () {
    //HISTORY
    Route::resource('history', HistoryController::class);

    // USERS
    Route::prefix('users')->group(function () {
        Route::get('getAllCustomers', [UsersController::class, 'getAllCustomers']);
        Route::get('getAllProviders', [UsersController::class, 'getAllProviders']);
        Route::get('banUser', [UsersController::class, 'banUser']);
        Route::get('unbanUser', [UsersController::class, 'unbanUser']);
        Route::get('approveProvider', [UsersController::class, 'approveProvider']);
        Route::get('purge', [UsersController::class, 'purge']);
    });
    Route::resource('users', UsersController::class);

});

Route::prefix('provider')->group(function () {
    Route::resource('menu', MenuController::class);
});

Route::prefix('pesanan')->group(function () {
    Route::get('getPesananProvider', [PesananController::class, 'getPesananProvider']);
});
Route::resource('pesanan', PesananController::class);
