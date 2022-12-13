<?php

use App\Http\Controllers\LoginRegis\LoginController;
use App\Http\Controllers\LoginRegis\RegisterController;
use App\Http\Controllers\ResourceControllers\HistoryLogController;
use App\Http\Controllers\ResourceControllers\HistoryMenuController;
use App\Http\Controllers\ResourceControllers\MenuController;
use App\Http\Controllers\ResourceControllers\PesananController;
use App\Http\Controllers\ResourceControllers\UsersController;
use App\Models\Users;
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

// CHANGELOG 
// - admin group gone
// - added log 

// USER UTILITY //////////////////////////////////////////////////////////////
// untuk cek siapa yang login
Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return response()->json([
        "status" => 'success',
        'message' => 'successfully fetched current user',
        "data" => $request->user(),
    ],200);
});
Route::middleware('auth:sanctum')->get('/mini-me', function (Request $request) {
    // $user = new Users((Array)json_decode($request->user()));
    $user = $request->user();
    return response()->json([
        "status" => 'success',
        'message' => 'successfully fetched current user',
        "data" => [
            "users_id" => $user->users_id,
            "users_nama" => $user->users_nama,
            "users_role" => $user->users_role,
            "users_saldo" => $user->users_saldo,
        ],
    ],200);
});
Route::middleware(['auth:sanctum','role:provider'])->get('/mystat', function (Request $request) {
    $user = $request->user();
    $header_pemesanan = Users::find($user->users_id)->HistoryPemesananProvider();
    $thismonth_delivery = $header_pemesanan->where('pemesanan_status','diterima')
        ->withCount('DetailPemesanan')->first();
    $totalpendapatan = $header_pemesanan->whereDoesntHave('DetailPemesanan',function(Builder $query){
            $query->where('detail_status','belum dikirim')
                ->orWhere('detail_status','terkirim');
        })
        ->sum('pemesanan_total');
    $made_delivery = $header_pemesanan->where('pemesanan_status','diterima')
        ->withCount(['DetailPemesanan' => function(Builder $query) {
            $query->where('detail_status','terkirim');
        }])
        ->first();
    return response()->json([
        "status" => 'success',
        'message' => 'successfully fetched provider stat',
        "data" => [
            "thismonth_delivery" => $thismonth_delivery->detail_pemesanan_count,
            "total_pendapatan" => $totalpendapatan,
            "made_delivery" => $made_delivery->detail_pemesanan_count,
        ],
    ],200);
});

// Tembak dulu sanctum/csrf-cookie untuk dapat csrf token
// https://laravel.com/docs/9.x/sanctum#cors-and-cookies
// https://laravel.com/docs/9.x/sanctum#spa-authenticating
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/register', [RegisterController::class, 'register'])->name('register');
Route::middleware('auth:sanctum')->post('/logout', [LoginController::class, 'logout']);
////////////////////////////////////////////////////////////////////////////////


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
////////////////////////////////////////////////////////////////////////////////


// LOG /////////////////////////////////////////////////////////////////////////
// TODO middlewre
Route::resource('log', HistoryLogController::class);
// avaliable actions
// index
////////////////////////////////////////////////////////////////////////////////


// MENU ////////////////////////////////////////////////////////////////////////
Route::prefix('menu')->group(function () {
    Route::patch('/{id}/rate', [PesananController::class, 'rate']);
});
Route::resource('menu', MenuController::class);
// avaliable actions
// index, store, show, update, destroy
////////////////////////////////////////////////////////////////////////////////


// HISTORY MENU ////////////////////////////////////////////////////////////////
Route::resource('historyMenu', HistoryMenuController::class);
// avaliable actions
// index
////////////////////////////////////////////////////////////////////////////////


// PESANAN /////////////////////////////////////////////////////////////////////
Route::prefix('pesanan')->group(function () {
    Route::get('showDelivery', [PesananController::class,'showDelivery']);
    Route::post('{id}/reject', [PesananController::class,'reject']);
    Route::post('{id}/accept', [PesananController::class,'accept']);
    Route::post('deliver/{detail_id}', [PesananController::class,'kirim']);
    Route::post('receive/{detail_id}', [PesananController::class,'terima']);
});
Route::resource('pesanan', PesananController::class);
// avaliable actions
// index, store, show, update, destroy
////////////////////////////////////////////////////////////////////////////////





///////////////////////////////////////////////////////////////
// DEPRECATED ZONE
///////////////////////////////////////////////////////////////

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