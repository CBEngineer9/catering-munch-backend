<?php

namespace App\Http\Controllers\ResourceControllers;

use App\Http\Controllers\Controller;
use App\Models\HistoryPemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PesananController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pemesanan = HistoryPemesanan::all()->toArray();
        return response()->json($pemesanan,200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pemesanan = HistoryPemesanan::find($id);
        return response()->json($pemesanan,200);
    }

    /**
     * Get all pesanan to currently logged on provider
     *
     * @return \Illuminate\Http\Response
     */
    public function getPesananProvider()
    {
        // TODO id / auth?
        $currUser = Auth::user();
        if ($currUser->users_role == "provider") {
            $pemesanan = HistoryPemesanan::where("users_provider",$currUser);
            return response()->json($pemesanan,200);
        } else {
            return response([
                "status" => "forbidden"
            ],403);
        }
    }

    /**
     * Get all pesanan to currently logged on customer
     *
     * @return \Illuminate\Http\Response
     */
    public function getPesananCustomer()
    {
        // TODO id / auth?
        $currUser = Auth::user();
        if ($currUser->users_role == "customer") {
            $pemesanan = HistoryPemesanan::where("users_customer",$currUser);
            return response()->json($pemesanan,200);
        } else {
            return response([
                "status" => "forbidden"
            ],403);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
