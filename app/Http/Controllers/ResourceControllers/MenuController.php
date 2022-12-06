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
        // TODO pagination
        $new_menu = new Menu();
        $tablename = $new_menu->getTable();
        $columns = Schema::getColumnListing($tablename);
        $request->validate([
            "role" => ['nullable', Rule::in(['admin','customer','provider'])],
            "provider_id" => "nullable|exists:App/Model/Users,users_id",
            'sort' => 'nullable',
            'sort.column' => [ 'required_with:sort.type' , Rule::in($columns)],
            'sort.type' => ['required_with:sort.column', Rule::in(['asc','desc'])],
            'batch_size' => ["integer", "gt:0"],
        ]);

        $sort_column = $request->sort->column ?? "users_id";
        $sort_type = $request->sort->column ?? "asc";
        $batch_size = $request->batch_size ?? 10;

        // TODO perlu = ?
        $listMenu = Menu::withTrashed()->orderBy($sort_column,$sort_type);
        if ($request->has('role')) {
            $listMenu = $listMenu->where('users_role',$request->role);
        }
        if ($request->has('provider_id')) {
            $listMenu = $listMenu->where('users_id',$request->provider_id);
        }
        $listMenu = $listMenu->paginate($batch_size);
        return response()->json([
            'status' => "success",
            'message' => "successfully fetched all menu",
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
        // TODO upload foto, validation
        $currUser = new Users((Array)json_decode($request->user()));
        $validator = Validator::make($request->all(),[
            'menu_nama' => "string",
            'menu_foto' => "file|image|max:4194",
            'menu_harga' => "integer|gte:100",
            'menu_status' => [Rule::in(['tersedia','tidak tersedia'])],
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

        $validator->validated; // TODO
        
        if ($request->user()->isAdministrator()) {
            $users_id = $request->users_id;
        } else if ($request->user()->users_role === 'customer') {
            $users_id = $request->user()->users_id;
        } 
        
        $menu = new Menu();
        $menu->menu_nama = 
        $menu->users_id = $users_id;

        $hist = new HistoryMenu();
        $hist->history_menu_action = "Created menu";
        $hist->menu_id = $menu->menu_id;
        
        DB::beginTransaction();
        try {
            $menu->save();  // insert menu
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
        $menuTerpilih = Menu::find($id)->toArray();
        return response()->json($menuTerpilih,200);
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
        $request->validate([
            'menu_nama' => "nullable|string",
            "menu_foto" => "nullable|file|image|max:4194",
            'menu_harga' => "nullable|integer|gte:100",
            "menu_tanggal" => "nullable|date",
            'menu_status' => ["nullable", Rule::in(['tersedia','tidak tersedia'])]
        ]);

        $menuTerpilih = Menu::find($id);
        $columns = [
            "menu_nama",
            "menu_foto",
            "menu_harga",
            "menu_tanggal",
            "menu_status",
        ];
        foreach ($columns as $column) {
            if ($request->has($column)) {
                $menuTerpilih->$column = $request->$column;
            }
        }

        $edited = "";
        foreach ($request->all() as $req_name => $req_value) {
            $edited .= $req_name . ", ";
        }
        $edited = substr($edited,0,-2);

        $hist = new HistoryMenu();
        $hist->history_menu_action = "Edited " . $edited;
        $hist->menu_id = $id;

        return $hist;
        
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
