<?php

namespace App\Http\Controllers\ResourceControllers;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $menu = Menu::all()->toArray();
        return response()->json($menu,200);
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
        // TODO upload foto
        $menu = $request->validate([
            'menu_nama' => "string",
            'menu_foto' => "file|image|max:4194",
            'menu_harga' => "integer|gte:100",
            'menu_status' => [Rule::in(['tersedia','tidak tersedia'])],
        ]);

        $curr = $request->user();
        $menu = new Menu($request->all());
        $menu->users_id = $curr->users_id;
        $menu->save();

        return response()->json([
            'status' => 'success',
            'message' => "berhasil tambah menu",
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
        $menuTerpilih->save();

        return response()->json([
            'status' => 'success',
            'message' => 'berhasil update'
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
            'message' => 'menu deleted',
            'code' => 200
        ],200);
    }
}
