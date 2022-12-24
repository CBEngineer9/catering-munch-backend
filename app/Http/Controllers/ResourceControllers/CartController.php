<?php

namespace App\Http\Controllers\ResourceControllers;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Menu;
use App\Models\Users;
use App\Rules\UserRoleRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // authorize
        $this->authorize('viewAny',Cart::class);

        $currUser = new Users((Array)json_decode($request->user()));
        $user = $request->user();

        $validator = Validator::make($request->all(),[
            "customer_id" => [
                Rule::prohibitedIf(!$currUser->isAdministrator()),
                "exists:App\Models\Users,users_id", 
                new UserRoleRule("customer")],
        ]);
        if ($validator->fails()) {
            return response() ->json([
                'status' => 'unprocessable content',
                'message' => 'There are errors found on the data you have entered',
                'errors' => $validator->errors(),
            ],422);
        }

        if ($request->has("customer_id")) {
            $cart = Users::findOrFail($request->customer_id)->CartCustomer()->with(['Menu:menu_id,menu_nama'])->get();
        } else {
            // $cart = Users::find($user->users_id)->CartCustomer()->with(['Menu:menu_id,menu_nama'])->get();
            $cart = Users::where("users_role","provider")
                ->whereHas("CartProvider",function(Builder $query) use ($user) {
                    $query->where("users_customer",$user->users_id);
                })
                ->with([
                        "CartProvider" => function ($query) use ($user) {
                            $query->where("users_customer",$user->users_id)
                                ->with("Menu:menu_id,menu_nama");
                        }
                    ])
                ->withSum('CartProvider as sum_cart_jumlah', "cart_jumlah")
                ->withSum('CartProvider as sum_cart_total', "cart_total")
                ->get(["users_id","users_nama"]);
                // TODO beautyfy result
        }

        return response()->json([
            "status" => "success",
            "message" => "successfully fetched users cart",
            "data" => $cart
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
        $this->authorize('create',Cart::class);

        $user = $request->user();

        $validator = Validator::make($request->all(),[
            "menu_id" => ["required", "exists:App\Models\Menu,menu_id"],
            "cart_jumlah" => ["required", "integer", "gt:0", "lte:1000"],
            "cart_tanggal" => ["required", "date", "after_or_equal:now"],
        ]);
        if ($validator->fails()) {
            return response() ->json([
                'status' => 'unprocessable content',
                'message' => 'There are errors found on the data you have entered',
                'errors' => $validator->errors(),
            ],422);
        }

        // all good
        $menu = Menu::find($request->menu_id);
        Cart::insert([
            "users_customer" => $user->users_id,
            "users_provider" => Menu::find($request->menu_id)->users_id,
            "menu_id" => $request->menu_id,
            "cart_jumlah" => $request->cart_jumlah,
            "cart_total" => $menu->menu_harga * $request->cart_jumlah,
            "cart_tanggal" => $request->cart_tanggal,
        ]);

        return response()->json([
            "status" => "created",
            'message' => "successfully added item to cart"
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
        $cartTerpilih = Cart::findOrFail($id)->with(['Menu',"Customer"])->first();
        // return request()->user();
        // return $cartTerpilih;
        $this->authorize('view',$cartTerpilih);

        return response()->json([
            "status" => "success",
            "message" => "successfully fetched one cart entry",
            "data" => $cartTerpilih
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
        $cartTerpilih = Cart::findOrFail($id);
        $this->authorize('update',$cartTerpilih);

        $currUser = new Users((Array)json_decode($request->user()));
        
        $validator = Validator::make($request->all(),[
            "users_customer" => [
                "nullable",
                Rule::prohibitedIf(!$currUser->isAdministrator()),
                "exists:App\Models\Users,users_id", 
                new UserRoleRule("customer")],
            "menu_id" => ["nullable", "exists:app\Models\Menu,menu_id"],
            "cart_jumlah" => ["nullable", "integer", 'gt:0', "lt:1000"],
            "cart_tanggal" => "date",
        ]);
        if ($validator->fails()) {
            return response() ->json([
                'status' => 'unprocessable content',
                'message' => 'There are errors found on the data you have entered',
                'errors' => $validator->errors(),
            ],422);
        }

        // all good
        $columns = $cartTerpilih->getFillable();
        foreach ($columns as $column) {
            if ($request->has($column)) {
                $cartTerpilih->$column = $request->$column;
            }
        }
        if ($request->has('cart_jumlah') || $request->has('menu_id')) {
            $cartTerpilih->cart_total = $cartTerpilih->cart_jumlah * $cartTerpilih->Menu->menu_harga;
        }
        $cartTerpilih->save();
        return response()->json([
            'status' => 'created',
            'message' => "succesfully updated cart"
        ],201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cartTerpilih = Cart::findOrFail($id);
        $this->authorize('delete',$cartTerpilih);

        Cart::destroy($id);
        return response()->json([
            'status' => "success",
            'message' => "successfuly delete cart item"
        ],200);
    }

    /**
     * Clears users cart
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     **/
    public function clear(Request $request)
    {
        $this->authorize('clear',Cart::class);

        $user = $request->user();

        $userCarts = Cart::where("users_customer",$user->users_id)->get("cart_id")->map(function ($cartItem, $key) {
            return $cartItem->cart_id;
        });
        Cart::destroy($userCarts->all());
        return response()->json([
            'status' => "success",
            'message' => "successfuly empty users cart"
        ],200);
    }
}
