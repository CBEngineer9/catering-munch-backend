<?php

namespace App\Http\Controllers\ResourceControllers;

use App\Http\Controllers\Controller;
use App\Models\HistoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class HistoryLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // authorization (use admin role middleware)

        $new_log = new HistoryLog();
        $tablename = $new_log->getTable();
        $columns = Schema::getColumnListing($tablename);
        $request->validate([
            'sort' => 'nullable',
            'sort.column' => [ 'nullable' , Rule::in($columns)],
            'sort.type' => ['nullable', Rule::in(['asc','desc'])],
            'sort_column' => [Rule::in($columns)],
            'sort_type' => [Rule::in(['asc','desc'])],
            'batch_size' => ["nullable", "integer", "gt:0"],
            'date_lower' => ["nullable", 'date', "before:now"],
            'date_upper' => ["nullable", 'date', "before_or_equal:now"]
        ]);

        $sort_column = $request->sort['column'] ?? $request->sort_column ?? "log_timestamp";
        $sort_type = $request->sort['type'] ?? $request->sort_type ?? "desc";
        // $batch_size = $request->batch_size ?? 10;
        $date_lower = $request->date_lower ?? "1970-01-01";
        $date_upper = $request->date_upper ?? date("Y-m-d");

        $histLog = HistoryLog::with('Users:users_id,users_nama,users_role')->orderBy($sort_column,$sort_type);
        if ($date_lower) {
            $histLog = $histLog->whereDate('log_timestamp',">=",$date_lower);
        }
        if ($date_upper) {
            $histLog = $histLog->whereDate('log_timestamp',"<=",$date_upper);
        }
        if ($request->has('batch_size') && $request->batch_size !== null) {
            $histLog = $histLog->paginate($request->batch_size);
        } else {
            $histLog = $histLog->get();
        }
        return response()->json([
            'status' => "success",
            'message' => "successfully fetched history log",
            'data' => $histLog
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
