<?php

use App\Http\Controllers\LoginRegis\LoginController;
use App\Http\Controllers\LoginRegis\RegisterController;
use App\Http\Controllers\ResourceControllers\ReportController;
use App\Http\Controllers\ResourceControllers\CartController;
use App\Http\Controllers\ResourceControllers\HistoryLogController;
use App\Http\Controllers\ResourceControllers\HistoryMenuController;
use App\Http\Controllers\ResourceControllers\HistoryTopupController;
use App\Http\Controllers\ResourceControllers\MenuController;
use App\Http\Controllers\ResourceControllers\PesananController;
use App\Http\Controllers\ResourceControllers\UsersController;
use App\Models\DetailPemesanan;
use Illuminate\Database\Eloquent\Builder;
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

// FIXME put role middleware 
// [x] policy / middleware
// [x] provider menunggu = customer
// [x] history menu
// [x] history log
// [x] Plocy users
// [x] Tidy up

// CHANGELOG 

// USER UTILITY //////////////////////////////////////////////////////////////
// untuk cek siapa yang login
Route::middleware(['auth:sanctum','log'])->get('/me', [UsersController::class, 'getProfile']);
Route::middleware(['auth:sanctum','log'])->get('/mini-me', [UsersController::class, 'getProfileMini']);
// cek status admin dan provider
Route::middleware(['auth:sanctum','role:provider,admin','log'])->get('/mystat', [UsersController::class, 'getStatus']);

// Tembak dulu sanctum/csrf-cookie untuk dapat csrf token
// https://laravel.com/docs/9.x/sanctum#cors-and-cookies
// https://laravel.com/docs/9.x/sanctum#spa-authenticating
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/register', [RegisterController::class, 'register'])->name('register');
Route::middleware(['auth:sanctum','log'])->post('/logout', [LoginController::class, 'logout']);
Route::middleware(['auth:sanctum','log'])->patch('/topup', [UsersController::class, 'topup']);
Route::middleware(['auth:sanctum','log'])->patch('/topup-api', [UsersController::class, 'topupApi']);

Route::post('/login-api', [LoginController::class, 'loginApi'])->name('loginApi');
Route::middleware(['auth:sanctum','log'])->post('/logout-api', [LoginController::class, 'logoutApi']);


////////////////////////////////////////////////////////////////////////////////


// USERS ///////////////////////////////////////////////////////////
Route::middleware(['auth:sanctum','log'])->prefix('users')->group(function () {
    ////////////////////////////////////////////////////////////
    // DEPRECATED ZONE
    Route::get('getAllCustomers', [UsersController::class, 'getAllCustomers']);
    Route::get('getAllProviders', [UsersController::class, 'getAllProviders']);
    ///////////////////////////////////////////////////////////

    Route::patch('banUser/{id}', [UsersController::class, 'banUser']);
    Route::patch('unbanUser/{id}', [UsersController::class, 'unbanUser']);
    Route::patch('approveProvider/{id}', [UsersController::class, 'approveProvider']);
    Route::delete('purge/{id}', [UsersController::class, 'purge']);
    Route::post('restore/{id}', [UsersController::class, 'restore']);
});
Route::middleware(['auth:sanctum','log'])->resource('users', UsersController::class)
    ->missing(function (Request $request) {
        return response()->json([
            'status' => 'not found',
            'message' => 'we cannot find that resource'
        ],404);
    });
// avaliable actions
// index, store, show, update, destroy
// https://laravel.com/docs/9.x/controllers#actions-handled-by-resource-controller
////////////////////////////////////////////////////////////////////////////////


// LOG /////////////////////////////////////////////////////////////////////////
Route::middleware(['auth:sanctum','role:admin','log'])->resource('log', HistoryLogController::class);
// avaliable actions
// index
////////////////////////////////////////////////////////////////////////////////


// MENU ////////////////////////////////////////////////////////////////////////
Route::middleware(['auth:sanctum','log'])->resource('menu', MenuController::class);
// avaliable actions
// index, store, show, update, destroy
////////////////////////////////////////////////////////////////////////////////


// HISTORY MENU ////////////////////////////////////////////////////////////////
Route::middleware(['auth:sanctum','log'])->resource('historyMenu', HistoryMenuController::class);
// avaliable actions
// index
////////////////////////////////////////////////////////////////////////////////


// CART ////////////////////////////////////////////////////////////////////////
Route::middleware(['auth:sanctum','log'])->prefix('cart')->group(function () {
    Route::delete('/clear', [CartController::class, 'clear']);
});
Route::middleware(['auth:sanctum','log'])->resource('cart', CartController::class);
// avaliable actions
// index, store, show, update, destroy
////////////////////////////////////////////////////////////////////////////////


// PESANAN /////////////////////////////////////////////////////////////////////
Route::middleware(['auth:sanctum','log'])->prefix('pesanan')->group(function () {
    Route::get('showDelivery', [PesananController::class,'showDelivery']);
    Route::post('{id}/reject', [PesananController::class,'reject']);
    Route::post('{id}/accept', [PesananController::class,'accept']);
    Route::post('deliver/{detail_id}', [PesananController::class,'kirim']);
    Route::post('receive/{detail_id}', [PesananController::class,'terima']);
    Route::patch('/{id}/rate', [PesananController::class, 'rate']);
    Route::post('/pesanCart', [PesananController::class, 'pesanCart']);
});
Route::middleware(['auth:sanctum','log'])->resource('pesanan', PesananController::class);
// avaliable actions
// index, store, show, update, destroy
////////////////////////////////////////////////////////////////////////////////


/// HISTORY TOPUP //////////////////////////////////////////////////////////////
Route::middleware(['auth:sanctum','log'])->resource('historyTopup', HistoryTopupController::class);
// avaliable actions
// index
////////////////////////////////////////////////////////////////////////////////


/// REPORT /////////////////////////////////////////////////////////////////////
Route::middleware(['auth:sanctum', 'role:admin,provider','log'])->prefix('report')->group(function () {
    Route::get('/penjualan', [ReportController::class, 'penjualanTerbanyak']);
});
////////////////////////////////////////////////////////////////////////////////




///////////////////////////////////////////////////////////////
// DEPRECATED ZONE
///////////////////////////////////////////////////////////////

// Route::get('test', function () {
//     return DetailPemesanan::whereHas('HistoryPemesanan',function(Builder $query) {
//         $query->where('pemesanan_status','selesai')
//             // ->where('users_provider',$this->provider_id);
//             ;
//     })
//     ->with("Menu:menu_id,menu_nama")
//     ->addSelect(DB::raw('menu_id,sum(detail_jumlah) as total_terjual, sum(detail_total) as total_penjualan'))
//     ->groupBy(['menu_id'])
//     ->orderBy('total_penjualan')
//     ->get()
//     ->map(function($item){
//         return [
//             "menu_nama" => $item->menu->menu_nama,
//             "total_terjual" => $item->total_terjual,
//             "total_penjualn" => $item->total_penjualan,
//         ];
//     })
//     ;
// });

// endpoint admin
Route::prefix('admin')->group(function () {
    // USERS ///////////////////////////////////////////////////////////
    Route::prefix('users')->group(function () {
        ////////////////////////////////////////////////////////////
        // DEPRECATED ZONE
        Route::get('getAllCustomers', [UsersController::class, 'getAllCustomers']);
        Route::get('getAllProviders', [UsersController::class, 'getAllProviders']);
        ///////////////////////////////////////////////////////////

        Route::patch('banUser/{id}', [UsersController::class, 'banUser']);
        Route::patch('unbanUser/{id}', [UsersController::class, 'unbanUser']);
        Route::patch('approveProvider/{id}', [UsersController::class, 'approveProvider']);
        Route::delete('purge/{id}', [UsersController::class, 'purge']);
        Route::post('restore/{id}', [UsersController::class, 'restore']);
    });
    Route::resource('users', UsersController::class)
        ->missing(function (Request $request) {
            return response()->json([
                'status' => 'not found',
                'message' => 'we cannot find that resource'
            ],404);
        });
    // avaliable actions
    // index, store, show, update, destroy
    // https://laravel.com/docs/9.x/controllers#actions-handled-by-resource-controller
});

// endpoint providers
Route::prefix('provider')->group(function () {
    Route::prefix('pesanan')->group(function () {
        Route::get('getPesananProvider', [PesananController::class, 'getPesananProvider']);
    });
});

// endpoint customer
Route::prefix('customer')->group(function () {
    
});