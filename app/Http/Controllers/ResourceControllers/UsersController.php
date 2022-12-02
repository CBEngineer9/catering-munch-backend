<?php

namespace App\Http\Controllers\ResourceControllers;

use App\Http\Controllers\Controller;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $listUser = Users::withTrashed()->get()->all();
        return response()->json($listUser,200);
    }

    /**
     * Displays all customer
     *
     * @return \Illuminate\Http\Response
     **/
    public function getAllCustomers()
    {
        $listUser = Users::withTrashed()->where("users_role","customer")->get()->all();
        return response()->json($listUser,200);
    }

    /**
     * Displays all provider
     *
     * @return \Illuminate\Http\Response
     **/
    public function getAllProviders(Request $request)
    {
        $item = new Users();
        $tablename = $item->getTable();
        $columns = Schema::getColumnListing($tablename);
        return response()->json($columns,200);
        $request->validate([
            'sort.column',
            'sort.type',
            'batch_size',
            'batch',
        ]);
        $listUser = Users::withTrashed()->where("users_role","provider")->get()->all();
        return response()->json($listUser,200);
    }

    /**
     * Approve provider
     *
     * @param $id
     * @return \Illuminate\Http\Response
     **/
    public function approveProvider($id)
    {
        $userTerpilih = Users::find($id);
        $userTerpilih->users_status = "aktif";
        $userTerpilih->save();
        return response()->json([
            'status' => 'updated'
        ],200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // TODO
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
        $request->validate([
            "users_nama" => "required",
            "users_email" => "required | email | unique:users,users_email",
            "users_password" => "required | confirmed",
            "users_alamat" => "required",
            "users_telepon" => "required | numeric | digits_between:8,12 | unique:users,users_telepon",
            "users_role" => [Rule::in(["customer","provider"])],
            "tna" => "accepted",
        ]);

        // all good
        Users::create([
            "users_nama" => $request->users_nama,
            "users_email" => $request->users_email,
            "users_password" => $request->users_password,
            "users_alamat" => $request->users_alamat,
            "users_telepon" => $request->users_telepon,
            "users_role" => $request->users_role,
            "users_status" => $request->users_role == "customer" ? "aktif" : "menunggu"
        ]);

        return response([
            "status" => "created"
        ],201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Users::withTrashed()->find($id);
        return response()->json($user,200);
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
        // TODO
    }

    /**
     * Ban the user
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function banUser($id)
    {
        $userTerpilih = Users::find($id);
        $userTerpilih->users_status = 'banned';
        return response([
            'status' => "success ban",
        ],200);
    }

    /**
     * Unban the user
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function unbanUser($id)
    {
        $userTerpilih = Users::find($id);
        $userTerpilih->users_status = 'aktif';
        return response([
            'status' => "success unban",
        ],200);
    }

    /**
     * Soft Deletes the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Users::withTrashed()->destroy($id);
        return response([
            'status' => "deleted",
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function purge($id)
    {
        Users::withTrashed()->find($id)->forceDelete();
        return response([
            'status' => "deleted",
        ],200);
    }
}
