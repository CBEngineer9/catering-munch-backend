<?php

namespace App\Http\Controllers\ResourceControllers;

use App\Http\Controllers\Controller;
use App\Models\HistoryTopup;
use App\Models\Users;
use App\Rules\UserRoleRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class HistoryTopupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // TODO authorization 
        
        $currUser = new Users((Array)json_decode($request->user()));
        $userId = $request->user()->users_id;

        $new_topup = new HistoryTopup();
        $tablename = $new_topup->getTable();
        $columns = Schema::getColumnListing($tablename);
        $request->validate([
            'customer_id' => [
                Rule::prohibitedIf(!$currUser->isAdministrator()), 
                "exists:App\Models\Users,users_id", 
                new UserRoleRule("customer")],
            'sort' => 'nullable',
            'sort.column' => ['nullable' , Rule::in($columns)],
            'sort.type' => ['nullable', Rule::in(['asc','desc'])],
            'batch_size' => ["nullable", "integer", "gt:0"],
            'date_lower' => ["nullable", 'date', "before:now"],
            'date_upper' => ["nullable", 'date', "before_or_equal:now"]
        ]);

        $sort_column = $request->sort['column'] ?? "topup_tanggal";
        $sort_type = $request->sort['type'] ?? "desc";
        $batch_size = $request->batch_size ?? 10;
        $date_lower = $request->date_lower ?? "1970-01-01";
        $date_upper = $request->date_upper ?? date("Y-m-d");

        $histTopup = HistoryTopup::with('Users:users_id,users_nama,users_role')->orderBy($sort_column,$sort_type);
        
        // show only owned topup, unless specified by ADMIN
        if ($request->user()->users_role === 'admin') {
            if ($request->has('users_id')) {
                $histTopup = $histTopup->whereRelation("Menu",'users_id',$request->provider_id);
            }
        } else {
            $histTopup = $histTopup->whereRelation("Users",'users_id',$userId);
        }
        if ($date_lower) {
            $histTopup = $histTopup->whereDate('topup_tanggal',">=",$date_lower);
        }
        if ($date_upper) {
            $histTopup = $histTopup->whereDate('topup_tanggal',"<=",$date_upper);
        }
        $histTopup = $histTopup->paginate($batch_size);
        return response()->json([
            'status' => "success",
            'message' => "successfully fetched history topup",
            'data' => $histTopup
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
        abort(404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        abort(404);
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
        abort(404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        abort(404);
    }
}
