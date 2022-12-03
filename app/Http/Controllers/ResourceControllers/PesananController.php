<?php

namespace App\Http\Controllers\ResourceControllers;

use App\Http\Controllers\Controller;
use App\Models\DetailPemesanan;
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
        return response()->json([
            'status' => "success",
            'message' => "successfully fetched data",
            'data' => $pemesanan
        ],200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // TODO store
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
        return response()->json([
            'status' => "success",
            'message' => "successfully fetched data",
            'data' => $pemesanan
        ],200);
    }

    /**
     * Get all pesanan to currently logged on provider
     *
     * @return \Illuminate\Http\Response
     */
    public function getPesananProvider(Request $request)
    {
        $currUser = $request->user();
        if ($currUser->users_role == "provider") {
            $pemesanan = HistoryPemesanan::where("users_provider",$currUser->users_id)->get()->toArray();
            return response()->json([
                "status" => "success",
                "message" => "successfuly fetched pemesanan",
                "data" => $pemesanan
            ],200);
            // return response()->dro('success',200,'successfuly fetched pemesanan',$pemesanan);
            // return response()->caps('success');
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
    public function getPesananCustomer(Request $request)
    {
        $currUser = $request->user();
        if ($currUser->users_role == "customer") {
            $pemesanan = HistoryPemesanan::where("users_customer",$currUser)->get()->toArray();
            return response()->json([
                "status" => "success",
                "message" => "successfuly fetched pemesanan",
                "data" => $pemesanan
            ],200);
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
        abort(404);
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
        // TODO update
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DetailPemesanan::where("pemesanan_id",$id)->delete();
        HistoryPemesanan::destroy($id);
        return response()->json([
            'status' => "success",
            'message' => "successfully deleted pesanan"
        ], 200);
    }
}
