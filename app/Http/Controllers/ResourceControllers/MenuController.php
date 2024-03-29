<?php

namespace App\Http\Controllers\ResourceControllers;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\HistoryMenu;
use App\Models\Menu;
use App\Models\Users;
use App\Rules\UserRoleRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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
        // authorization
        $this->authorize('viewAny', Menu::class);

        $user = $request->user();

        $new_menu = new Menu();
        $tablename = $new_menu->getTable();
        $columns = Schema::getColumnListing($tablename);
        $validator = Validator::make($request->all(),[
            "provider_id" => ["nullable", "exists:App\Models\Users,users_id", new UserRoleRule("provider")],
            'sort' => 'nullable',
            'sort.column' => ['required_with:sort.type' , Rule::in($columns)],
            'sort.type' => ['required_with:sort.column', Rule::in(['asc','desc'])],
            'sort_column' => [Rule::in($columns)],
            'sort_type' => [Rule::in(['asc','desc'])],
            'batch_size' => ["nullable", "integer", "gt:0"],
            'menu_nama' =>  ['nullable', "string"],
            "menu_status" => ['nullable', Rule::in(['tersedia','tidak tersedia'])]
        ]);
        if ($validator->fails()) {
            if ($user == null) {
                $users_id = 1;
            } else {
                $users_id = $user->users_id;
            }
            LogHelper::log("alert","failed menu fetch attempt","from " . $request->ip(). ", reason: validation fail",$users_id);
            
            return response() ->json([
                'status' => 'unprocessable content',
                'message' => 'There are errors found on the data you have entered',
                'errors' => $validator->errors(),
            ],422);
        }

        $sort_column = $request->sort['column'] ?? $request->sort_column ?? "menu_id";
        $sort_type = $request->sort['type'] ?? $request->sort_type ?? "asc";
        // $batch_size = $request->batch_size ?? 10;

        $listMenu = Menu::orderBy($sort_column,$sort_type);
        if ($request->has('provider_id') && $request->provider_id != null) {
            $listMenu = $listMenu->where('users_id',$request->provider_id);
        }
        if ($request->has('menu_nama')) {
            $listMenu = $listMenu->where('menu_nama','like','%'.$request->menu_nama.'%');
        }
        if ($request->has('menu_status') && $request->menu_status != null) {
            $listMenu = $listMenu->where('menu_status',$request->menu_status);
        }

        $listMenu = $listMenu->with('Users:users_id,users_nama');

        if ($request->has('batch_size') && $request->batch_size !== null) {
            $listMenu = $listMenu->paginate($request->batch_size);
        } else {
            $listMenu = $listMenu->get();
        }

        if ($user == null) {
            $users_id = 1;
        } else {
            $users_id = $user->users_id;
        }
        LogHelper::log("info","successful menu fetch","from " . $request->ip(),$users_id);
        
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
        // authorization
        $this->authorize('create', Menu::class);

        $currUser = new Users((Array)json_decode($request->user()));
        if ($currUser == null) {
            $users_id = 1;
        } else {
            $users_id = $request->user()->users_id;
        }
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
            LogHelper::log("alert","failed menu create attempt","from " . $request->ip(). ", reason: validation fail: ". $validator->errors(),$users_id);
            return response() ->json([
                'status' => 'unprocessable content',
                'message' => 'There are errors found on the data you have entered',
                'errors' => $validator->errors(),
            ],422);
        }

        $path = $request->menu_foto->store('menu','public');
        
        if ($request->user()->isAdministrator()) { // get user_id from form if admin
            $users_id = $request->users_id;
        } else if ($request->user()->users_role === 'provider') { // get user_id from user if provider
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
            LogHelper::log("error","failed menu create attempt","from " . $request->ip(). ", reason: server error",$users_id);
            return response()->json([
                'status' => "server error",
                'message' => "mysql error",
                "errors" => [
                    'mysql_error' => $th->getMessage()
                ]
            ],500);
        }

        LogHelper::log("info","successful menu create","from " . $request->ip(),$users_id);

        return response()->json([
            'status' => 'success',
            'message' => "successfully added menu",
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
        // authorize
        $menu = Menu::find($id);
        $this->authorize('view',$menu);
        $user = request()->user();

        $menuTerpilih = Menu::findOrFail($id)->toArray();

        if ($user == null) {
            $users_id = 1;
        } else {
            $users_id = $user->users_id;
        }
        LogHelper::log("info","successful menu show","from " . request()->ip(),$users_id);

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
        // authorize
        $menu = Menu::find($id);
        $this->authorize('update',$menu);

        $user = $request->user();
        if ($user == null) {
            $users_id = 1;
        } else {
            $users_id = $user->users_id;
        }
        
        $validator = Validator::make($request->all(),[
            'menu_nama' => "nullable|string",
            "menu_foto" => "nullable|file|image|max:4194",
            'menu_harga' => "nullable|integer|gte:100",
            "menu_tanggal" => "nullable|date",
            'menu_status' => ["nullable", Rule::in(['tersedia','tidak tersedia'])]
        ]);
        if ($validator->fails()) {
            LogHelper::log("alert","failed menu update attempt","from " . $request->ip(). ", reason: validation fail",$users_id);

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

        if ($request->has('menu_foto') && $request->menu_foto != null) {
            // delete old file
            $oldpath = $menuTerpilih->menu_foto;
            Storage::disk('public')->delete($oldpath);
            
            // add new path
            $path = $request->menu_foto->store('menu','public');
            $menuTerpilih->menu_foto = $path;
        }

        $edited = "";
        foreach ($request->all() as $req_name => $req_value) {
            if ($req_name != "_method") {
                $edited .= $req_name . ", ";
            }
        }
        $edited = substr($edited,0,-2);

        $hist = new HistoryMenu();
        $hist->history_menu_action = "Edited " . $edited;
        $hist->menu_id = $id;
        
        DB::beginTransaction();
        try {
            $menuTerpilih->save();
            $hist->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            LogHelper::log("error","failed menu update attempt","from " . $request->ip(). ", reason: server error",$users_id);
            return response()->json([
                'status' => "server error",
                'message' => "mysql error",
                "errors" => [
                    'mysql_error' => $th->getMessage()
                ]
            ],500);
        }

        LogHelper::log("info","successful menu update attempt","from " . $request->ip(),$users_id);

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
        // authorize
        $menu = Menu::find($id);
        $this->authorize('delete',$menu);

        $user = request()->user();
        if ($user == null) {
            $users_id = 1;
        } else {
            $users_id = $user->users_id;
        }

        $hist = new HistoryMenu();
        $hist->history_menu_action = "Deleted Menu";
        $hist->menu_id = $id;
        
        DB::beginTransaction();
        try {
            Menu::destroy($id);
            $hist->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            LogHelper::log("alert","failed menu delete attempt","from " . request()->ip(). ", reason: validation fail",$users_id);
            return response()->json([
                'status' => "server error",
                'message' => "mysql error",
                "errors" => [
                    'mysql_error' => $th->getMessage()
                ]
            ],500);
        }

        LogHelper::log("info","successful menu delete attempt","from " . request()->ip(),$users_id);
        
        return response()->json([
            'status' => 'success',
            'message' => 'successfully deleted menu',
            'code' => 200
        ],200);
    }
}
