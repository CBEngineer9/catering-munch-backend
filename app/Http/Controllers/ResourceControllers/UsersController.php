<?php

namespace App\Http\Controllers\ResourceControllers;

use App\Http\Controllers\Controller;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
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
        return response()->json([
            "status" => "success",
            "data" => $listUser
        ],200);
    }

    /**
     * Displays all customer
     *
     * @return \Illuminate\Http\Response
     **/
    public function getAllCustomers()
    {
        $listUser = Users::withTrashed()->where("users_role","customer")->get()->all();
        return response()->json([
            "status" => "success",
            "data" => $listUser
        ],200);
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
        // return response()->json($columns,200);
        $request->validate([
            'sort' => 'nullable',
            'sort.column' => [Rule::in($columns)],
            'sort.type' => [Rule::in(['asc','desc'])],
            'batch_size' => "integer|gt:0",
            'batch' => "integer|gt:0",
        ]);
        $listUser = Users::withTrashed()->where("users_role","provider")->get()->all();
        return response()->json([
            "status" => 'success',
            "data" => $listUser
        ],200);
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
            'status' => 'updated',
            "message" => "successfully approved provider"
        ],200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // TODO create
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
        $validator = Validator::make($request->all(),[
            "users_nama" => "nullable",
            "users_email" => "nullable | email | unique:users,users_email",
            "users_password" => "nullable | confirmed",
            "users_alamat" => "nullable",
            "users_telepon" => "nullable | numeric | digits_between:8,12 | unique:users,users_telepon",
            "users_role" => [Rule::in(["customer","provider"])],
        ]);
        // TODO update
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
        $userTerpilih->save();
        return response([
            'status' => "success",
            'message' => "successfuly banned user"
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
        $userTerpilih->save();
        return response([
            'status' => "success",
            'message' => "successfuly unban user"
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
            'status' => "success",
            'message' => "successfuly soft delete user"
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
            'status' => "success",
            'message' => "successfuly delete user"
        ],200);
    }
}
