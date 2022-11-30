<?php

namespace App\Http\Controllers\WebControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function add(Request $request)
    {
        return redirect('/provider/menu')->with("message", "Berhasil add menu!");
    }
}
