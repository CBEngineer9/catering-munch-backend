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
        return response($menu,200);
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
            'menu_foto' => "file",
            'menu_harga' => "integer|gte:100",
            'menu_status' => [Rule::in(['tersedia','tidak tersedia'])],
        ]);

        $curr = Auth::user();
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
        return response($menuTerpilih,200);
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
            'menu_nama' => "string",
            'menu_harga' => "integer|gte:100",
            'menu_status' => [Rule::in(['tersedia','tidak tersedia'])]
        ]);

        $menuTerpilih = Menu::find($id);
        $menuTerpilih->menu_nama = $request->menu_nama;
        $menuTerpilih->menu_harga = $request->menu_harga;
        $menuTerpilih->menu_status = $request->menu_status;
        // TODO TANGGAL MASAK?
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
