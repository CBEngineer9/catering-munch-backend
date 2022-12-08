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
    public function index(Request $request)
    {
        $new_user = new Users();
        $tablename = $new_user->getTable();
        $columns = Schema::getColumnListing($tablename);
        $request->validate([
            'sort' => 'nullable',
            'sort.column' => [ 'required_with:sort.type' , Rule::in($columns)],
            'sort.type' => ['required_with:sort.column', Rule::in(['asc','desc'])],
            'batch_size' => ["integer", "gt:0"],
            "users_role" => ['nullable', Rule::in(['admin','customer','provider'])],
            "users_status" => ['nullable', Rule::in(['banned', 'aktif', 'menunggu'])],
            "users_nama" => ['nullable', "string"],
        ]);

        $sort_column = $request->sort['column'] ?? "users_id";
        $sort_type = $request->sort['type'] ?? "asc";
        $batch_size = $request->batch_size ?? 10;

        $listUser = Users::withTrashed()->orderBy($sort_column,$sort_type);
        if ($request->has('users_role')) {
            $listUser = $listUser->where('users_role',$request->users_role);
        }
        if ($request->has('users_status')) {
            $listUser = $listUser->where('users_status',$request->users_status);
        }
        if ($request->has('users_nama')) {
            $listUser = $listUser->where('users_nama','like','%'.$request->users_nama.'%');
        }
        $listUser = $listUser->paginate($batch_size);
        return response()->json([
            "status" => "success",
            "message" => "successfully fetched all user",
            "data" => $listUser
        ],200);
    }

    /**
     * Displays all customer
     *
     * @deprecated This method is no longer acceptable get all customer. Use fetch with request body instead.
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
        $userTerpilih = Users::findOrFail($id);
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
            "users_role" => ["required", Rule::in(["customer","provider"])],
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

        return response()->json([
            "status" => "created",
            'message' => "successfully created user"
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
        $user = Users::withTrashed()->findOrFail($id);
        return response()->json([
            "status" => "success",
            "message" => "successfully fetched user",
            "data" => $user
        ],200);
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
            "users_role" => ["nullable", Rule::in(["customer","provider"])],
        ]);
        if ($validator->fails()) {
            return response() ->json([
                'status' => 'unprocessable content',
                'message' => 'There are errors found on the data you have entered',
                'errors' => $validator->errors(),
            ],422);
        }

        // all good
        $userTerpilih = Users::findOrFail($id);
        $columns = $userTerpilih->getFillable();
        foreach ($columns as $column) {
            if ($request->has($column)) {
                $userTerpilih->$column = $request->$column;
            }
        }
        $userTerpilih->save();
        return response()->json([
            'status' => 'created',
            'message' => "succesfully updated user"
        ],201);
    }

    /**
     * Ban the user
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function banUser($id)
    {
        $userTerpilih = Users::findOrFail($id);
        if ($userTerpilih->users_status == 'banned') {
            return response()->json([
                'status' => "bad request",
                'message' => "user already banned"
            ],400);
        }
        $userTerpilih->users_status = 'banned';
        $userTerpilih->save();
        return response()->json([
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
        $userTerpilih = Users::findOrFail($id);
        if ($userTerpilih->users_status == 'aktif') {
            return response()->json([
                'status' => "bad request",
                'message' => "user is not banned"
            ],400);
        }
        $userTerpilih->users_status = 'aktif';
        $userTerpilih->save();
        return response()->json([
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
        Users::destroy($id);
        return response()->json([
            'status' => "success",
            'message' => "successfuly soft delete user"
        ],200);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        Users::withTrashed()->findOrFail($id)->restore();
        return response()->json([
            'status' => "success",
            'message' => "successfuly restore user"
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
        Users::withTrashed()->findOrFail($id)->forceDelete();
        return response()->json([
            'status' => "success",
            'message' => "successfuly delete user"
        ],200);
    }
}
