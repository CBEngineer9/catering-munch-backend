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

        $new_menu = new HistoryLog();
        $tablename = $new_menu->getTable();
        $columns = Schema::getColumnListing($tablename);
        $request->validate([
            'sort' => 'nullable',
            'sort.column' => [ 'nullable' , Rule::in($columns)],
            'sort.type' => ['nullable', Rule::in(['asc','desc'])],
            'batch_size' => ["nullable", "integer", "gt:0"],
            'date_lower' => ["nullable", 'date', "before:now"],
            'date_upper' => ["nullable", 'date', "before_or_equal:now"]
        ]);

        $sort_column = $request->sort['column'] ?? "log_timestamp";
        $sort_type = $request->sort['type'] ?? "desc";
        $batch_size = $request->batch_size ?? 10;

        $histLog = HistoryLog::orderBy($sort_column,$sort_type);
        if ($request->has('date_lower')) {
            $histLog = $histLog->where('log_timestamp',">=",$request->date_lower);
        }
        if ($request->has('date_upper')) {
            $histLog = $histLog->where('log_timestamp',"<=",$request->date_upper);
        }
        $histLog = $histLog->paginate($batch_size);
        return response()->json([
            'status' => "success",
            'message' => "successfully fetched history menu",
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