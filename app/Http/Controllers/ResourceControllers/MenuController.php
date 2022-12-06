<?php

namespace App\Http\Controllers\ResourceControllers;

use App\Http\Controllers\Controller;
use App\Models\HistoryMenu;
use App\Models\Menu;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $new_menu = new Menu();
        $tablename = $new_menu->getTable();
        $columns = Schema::getColumnListing($tablename);
        $request->validate([
            "provider_id" => "nullable|exists:App\Models\Users,users_id",
            'sort' => 'nullable',
            'sort.column' => [ 'nullable' , Rule::in($columns)],
            'sort.type' => ['nullable', Rule::in(['asc','desc'])],
            'batch_size' => ["nullable", "integer", "gt:0"],
        ]);

        $sort_column = $request->sort['column'] ?? "menu_id";
        $sort_type = $request->sort['type'] ?? "asc";
        $batch_size = $request->batch_size ?? 10;

        $listMenu = Menu::withTrashed()->orderBy($sort_column,$sort_type);
        if ($request->has('provider_id')) {
            $listMenu = $listMenu->where('users_id',$request->provider_id);
        }
        $listMenu = $listMenu->paginate($batch_size);
        return response()->json([
            'status' => "success",
            'message' => "successfully fetched menu",
            'data' => $listMenu
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
        // TODO upload foto, validation, auth provider only
        $currUser = new Users((Array)json_decode($request->user()));
        $validator = Validator::make($request->all(),[
            'menu_nama' => "required|string",
            'menu_foto' => "required|file|image|max:4194",
            'menu_harga' => "required|integer|gte:100",
            'menu_status' => ["required", Rule::in(['tersedia','tidak tersedia'])],
            'users_id' => [
                Rule::prohibitedIf(!$currUser->isAdministrator()), 
                Rule::requiredIf($currUser->isAdministrator()), 
                "exists:App\Models\Users,users_id" 
            ],
        ]);
        if ($validator->fails()) {
            return response() ->json([
                'status' => 'unprocessable content',
                'message' => 'There are errors found on the data you have entered',
                'errors' => $validator->errors(),
            ],422);
        }

        $path = $request->menu_foto->store('menu','public');
        
        if ($request->user()->isAdministrator()) {
            $users_id = $request->users_id;
        } else if ($request->user()->users_role === 'provider') {
            $users_id = $request->user()->users_id;
        } 
        
        DB::beginTransaction();
        try {
            $menu = new Menu();
            $menu->menu_nama = $request->menu_nama;
            $menu->menu_foto = $path;
            $menu->menu_harga = $request->menu_harga;
            $menu->menu_status = $request->menu_status;
            $menu->users_id = $users_id;
            $menu->save();  // insert menu

            $hist = new HistoryMenu();
            $hist->history_menu_action = "Created menu";
            $hist->menu_id = $menu->menu_id;
            $hist->save();  // insert history

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => "server error",
                'message' => "mysql error",
                "errors" => [
                    'mysql_error' => $th->getMessage()
                ]
            ],500);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Successfully added menu",
        ],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $menuTerpilih = Menu::findOrFail($id)->toArray();
        return response()->json([
            "status" => "success",
            "message" => "successfully fetched menu",
            "data" => $menuTerpilih
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
        return request($id); // BUG no workey
        return request($request->all());
        $validator = Validator::make($request->all(),[
            'menu_nama' => "nullable|string",
            "menu_foto" => "nullable|file|image|max:4194",
            'menu_harga' => "nullable|integer|gte:100",
            "menu_tanggal" => "nullable|date",
            'menu_status' => ["nullable", Rule::in(['tersedia','tidak tersedia'])]
        ]);
        if ($validator->fails()) {
            return response() ->json([
                'status' => 'unprocessable content',
                'message' => 'There are errors found on the data you have entered',
                'errors' => $validator->errors(),
            ],422);
        }

        $menuTerpilih = Menu::findOrFail($id);
        $columns = [
            "menu_nama",
            "menu_harga",
            "menu_tanggal",
            "menu_status",
        ];
        foreach ($columns as $column) {
            if ($request->has($column)) {
                $menuTerpilih->$column = $request->$column;
            }
        }
        if ($request->has('menu_foto')) {// TODO delete old file?
            $path = $request->menu_foto->store('menu','public');
            $menuTerpilih->menu_foto = $path;
        }

        return response($request->all());
        $edited = "";
        foreach ($request->all() as $req_name => $req_value) {
            error_log($req_value);
            $edited .= $req_name . ", ";
        }
        $edited = substr($edited,0,-2);

        $hist = new HistoryMenu();
        $hist->history_menu_action = "Edited " . $edited;
        $hist->menu_id = $id;

        return $hist; // TODO
        
        // DB::beginTransaction();
        // try {
        //     $menuTerpilih->save();
        //     $hist->save();
        //     DB::commit();
        // } catch (\Throwable $th) {
        //     DB::rollback();
        //     return response()->json([
        //         'status' => "server error",
        //         'message' => "mysql error",
        //         "errors" => [
        //             'mysql_error' => $th->getMessage()
        //         ]
        //     ],500);
        // }

        return response()->json([
            'status' => 'success',
            'message' => 'successfully update menu'
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Menu::destroy($id);
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully deleted menu',
            'code' => 200
        ],200);
    }
}
