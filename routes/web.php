<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Models\HistoryLog;
use App\Models\Menu;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



//LOGOUT
Route::get('logout', function () {
    auth()->logout();
    return redirect()->route('view-login');
});

//LOGIN
Route::get('/', function () {
    return view('login');
})->name('view-login');
Route::post('login', [LoginController::class, 'login'])->name('login');

//REGISTER
Route::get('register', function () {
    return view('register');
})->name('view-register');
Route::post('register', [RegisterController::class, 'register'])->name('register');

//ADMIN
Route::prefix('admin')->middleware("CekLogin:admin")->group(function () {
    //DASHBOARD
    Route::get('/', function () {
        $countRegistered = Users::where('users_role', "<>", "admin")->count();
        $countCustomer = Users::where("users_role", "customer")->count();
        $countProvider = Users::where('users_role', "provider")->count();
        $countUnverified = Users::where('users_status', "menunggu")->count();
        $waitingList = Users::where("users_role", "provider")->where('users_status', "menunggu")->get();
        return view('admin.index', compact("countRegistered", "countCustomer", "countProvider", "countUnverified", "waitingList"));
    })->name('view-admin');
    Route::post('approve/{id}', [AdminController::class, 'approve'])->name('admin-approve');
    //CUSTOMERS
    Route::get('customers', function (Request $request) {
        $search = $request->search;
        $customerList = Users::where("users_role", "customer")->where('users_nama', "LIKE", "%" . $search . "%")->get();
        return view('admin.customers', compact("customerList"));
    })->name('view-admin-customers');
    //PROVIDERS
    Route::get('providers', function (Request $request) {
        $search = $request->search;
        $providerList = Users::where("users_role", "provider")->where('users_nama', "LIKE", "%" . $search . "%")->get();
        return view('admin.providers', compact("providerList"));
    })->name('view-admin-providers');
    Route::post('ban/{id}', [AdminController::class, 'ban'])->name('admin-ban');
    //HISTORY
    Route::resource('history', HistoryController::class);
    // Route::get('history', function (Request $request) {
    //     $search = $request->search;
    //     if ($search) {
    //         $logList = HistoryLog::whereDate("log_datetime", $search)->get();
    //     } else {
    //         $logList = HistoryLog::all();
    //     }
    //     return view('admin.history', compact("logList", "search"));
    // });
});

//CUSTOMER
Route::prefix("customer")->middleware("CekLogin:customer")->group(function () {
    Route::get('/', function () {
        return view('customer.index');
    })->name('view-customer');
});

//PROVIDER
Route::prefix("provider")->middleware("CekLogin:provider")->group(function () {
    Route::get('/', function () {
        return view('provider.index');
    })->name('view-provider');
    Route::get('/menu', function (Request $request) {
        $menuList = Menu::where('users_id', auth()->user()->users_id)->get();
        return view('provider.menus', compact("menuList"));
    });
    Route::get('/menu/add', function () {
        return view('provider.add');
    });
    Route::post('/menu/add', [ProviderController::class, 'add']);
});
