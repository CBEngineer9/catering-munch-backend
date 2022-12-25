<?php

namespace App\Http\Controllers\ResourceControllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\PemesananResource;
use App\Models\Cart;
use App\Models\DetailPemesanan;
use App\Models\HistoryPemesanan;
use App\Models\Menu;
use App\Models\Users;
use App\Notifications\OrderMadeNotif;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PesananController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // brokey do not use
        // $this->authorizeResource(HistoryPemesanan::class, 'pesanan');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // authorize
        $this->authorize('viewAny', HistoryPemesanan::class);

        $new_menu = new HistoryPemesanan();
        $tablename = $new_menu->getTable();
        $columns = Schema::getColumnListing($tablename);
        $request->validate([
            'sort' => 'nullable',
            'sort.column' => [ 'nullable' , Rule::in($columns)],
            'sort.type' => ['nullable', Rule::in(['asc','desc'])],
            'batch_size' => ["nullable", "integer", "gt:0"],
            'date_lower' => ["nullable", 'date', "before:now"],
            'date_upper' => ["nullable", 'date', "before_or_equal:now"],
            "pemesanan_status" => ["nullable", Rule::in(['menunggu','ditolak','diterima','selesai'])],
        ]);

        $sort_column = $request->sort['column'] ?? "pemesanan_id";
        $sort_type = $request->sort['type'] ?? "asc";
        $batch_size = $request->batch_size ?? 10;
        $date_lower = $request->date_lower ?? "1970-01-01";
        $date_upper = $request->date_upper ?? date("Y-m-d");

        $currUser = $request->user();
        $pemesanan = HistoryPemesanan::orderBy($sort_column,$sort_type)
            ->with("UsersCustomer:users_id,users_nama,users_alamat,users_telepon")
            ->with("UsersProvider:users_id,users_nama")
            ->whereDate('created_at',"<=",$date_upper)
            ->whereDate('created_at',">=",$date_lower);

        if ($request->has('pemesanan_status') && $request->pemesanan_status != "") {
            $pemesanan->where("pemesanan_status",$request->pemesanan_status);
        }

        if ($currUser->users_role == 'admin') {
            $pemesanan = $pemesanan->paginate($batch_size);
            return response()->json([
                'status' => "success",
                'message' => "successfully fetched all data",
                'data' => $pemesanan
            ],200);
        } else if ($currUser->users_role == "provider") {
            $pemesanan = $pemesanan->where("users_provider",$currUser->users_id)
                ->paginate($batch_size);
            return response()->json([
                "status" => "success",
                "message" => "successfuly fetched pemesanan provider",
                "data" => $pemesanan
            ],200);
            // return response()->dro('success',200,'successfuly fetched pemesanan',$pemesanan);
            // return response()->caps('success');
        } else if ($currUser->users_role == "customer") {
            $pemesanan = $pemesanan->where("users_customer",$currUser->users_id)
                ->paginate($batch_size);
            return response()->json([
                "status" => "success",
                "message" => "successfuly fetched pemesanan customer",
                "data" => $pemesanan
            ],200);
        } else {
            return response()->json([
                "status" => "forbidden"
            ],403);
        }
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
     * only customers or admin can create pesanan
     * customer dont need to give their id, admin need to supply a customer id
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // authorize
        $this->authorize('create', HistoryPemesanan::class);

        $currUser = new Users((Array)json_decode($request->user()));
        $statusList = [
            'belum dikirim',
            'terkirim',
            'diterima'
        ];
        // customer tidak pelu give id
        $validator = Validator::make($request->all(),[
            "pemesanan_id" => "prohibited",
            "users_customer" => [
                Rule::prohibitedIf(!$currUser->isAdministrator()), 
                Rule::requiredIf($currUser->isAdministrator()), 
                "exists:App\Models\Users,users_id" 
            ],
            "users_provider" => "required | exists:App\Models\Users,users_id",
            "details" => "required",
            "details.*.detail_id" => "prohibited",
            "details.*.menu_id" => "required | exists:App\Models\Menu,menu_id",
            "details.*.detail_jumlah" => "required | integer",
            "details.*.detail_tanggal" => "required | date",
            "details.*.detail_status" => [
                Rule::prohibitedIf(!$currUser->isAdministrator()), 
                Rule::requiredIf($currUser->isAdministrator()), 
                Rule::in($statusList)
            ],
        ]);
        if ($validator->fails()) {
            return response() ->json([
                'status' => 'unprocessable content',
                'message' => 'There are errors found on the data you have entered',
                'errors' => $validator->errors(),
            ],422);
        }

        if ($request->user()->isAdministrator()) {
            $users_customer = $request->users_customer;
        } else if ($request->user()->users_role === 'customer') {
            $users_customer = $request->user()->users_id;
        } 
        
        // money check
        $details = $request->details;
        $total_price = 0;
        foreach ($details as $detail) {
            $total_price += Menu::find($detail['menu_id'])->menu_harga;
        }
        if ($request->user()->users_saldo < $total_price) {
            return response() ->json([
                'status' => 'payment required',
                'message' => 'Your credit is not enough to buy these item'
            ],402);
        }

        // all good
        DB::beginTransaction();
        try {
            $total = 0;
            $historyPemesanan = new HistoryPemesanan();
            $historyPemesanan->users_provider = $request->users_provider;
            $historyPemesanan->users_customer = $users_customer;
            $historyPemesanan->pemesanan_status = 'menunggu';
            $historyPemesanan->pemesanan_jumlah = count($details);
            $historyPemesanan->pemesanan_total = $total_price;
            $historyPemesanan->pemesanan_rating = 0;
            $historyPemesanan->save();
            
            foreach ($details as $detail) {
                $menu = Menu::find($detail['menu_id']);
                $total += $menu->menu_harga;
                
                $detail_model = new DetailPemesanan();
                $detail_model->pemesanan_id = $historyPemesanan->pemesanan_id;
                $detail_model->menu_id = $detail['menu_id'];
                $detail_model->detail_jumlah = $detail['detail_jumlah'];
                $detail_model->detail_total = $detail['detail_jumlah'] * $menu->menu_harga;
                $detail_model->detail_tanggal = $detail['detail_tanggal'];
                if ($request->has('detail_status')) {
                    $status = $request->detail_status;
                } else {
                    $status = 'belum dikirim';
                }
                $detail_model->detail_status = $status;
                $detail_model->save();
            }
            // transfer money


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
        
        $provider = Users::find($request->users_provider);
        $provider->notify(new OrderMadeNotif($historyPemesanan));

        return response()->json([
            'status' => 'created',
            'message' => 'successfully created pemesanan'
        ]);
    }

    /**
     * Move customer cart into real pesanan
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     **/
    public function pesanCart(Request $request)
    {
        // authorize
        $this->authorize('create', HistoryPemesanan::class);

        $currUser = new Users((Array)json_decode($request->user()));
        $users_customer = $request->user()->users_id;

        $cartFilter = function ($query) use ($users_customer) {
            $query
                ->where("users_customer",$users_customer)
                ->with("Menu:menu_id,menu_nama");
        };

        $carted_provider = Users::where("users_role","provider")
            ->whereHas("CartProvider",function(Builder $query) use ($users_customer) {
                $query->where("users_customer",$users_customer);
            })
            ->withSum(['CartProvider as sum_cart_jumlah' => $cartFilter], "cart_jumlah")
            ->withSum(['CartProvider as sum_cart_total' => $cartFilter], "cart_total")
            ->get();

        if ($carted_provider == null) {
            return response() ->json([
                'status' => 'not found',
                'message' => 'Your cart is empty'
            ],404);
        }
        
        // money check
        $total_price = 0;
        foreach ($carted_provider as $cart) {
            
            $total_price += $cart->sum_cart_total;
            if ($request->user()->users_saldo < $total_price) {
                return response() ->json([
                    'status' => 'payment required',
                    'message' => 'Your credit is not enough to buy these item'
                ],402);
            }
        }

        // all good

        $historyPemesanan = new HistoryPemesanan();

        DB::beginTransaction();
        try {
            foreach ($carted_provider as $provider) {
                $carts = $provider->CartProvider
                    ->where("users_customer",$users_customer);

                $total = 0;
                $historyPemesanan->users_provider = $provider->users_id;
                $historyPemesanan->users_customer = $users_customer;
                $historyPemesanan->pemesanan_status = 'menunggu';
                $historyPemesanan->pemesanan_jumlah = count($carts);
                $historyPemesanan->pemesanan_total = $total_price;
                $historyPemesanan->pemesanan_rating = 0;
                $historyPemesanan->save();
                
                foreach ($carts as $cart) {
                    $menu = Menu::find($cart['menu_id']);
                    $total += $menu->menu_harga;
                    
                    $detail_model = new DetailPemesanan();
                    $detail_model->pemesanan_id = $historyPemesanan->pemesanan_id;
                    $detail_model->menu_id = $cart['menu_id'];
                    $detail_model->detail_jumlah = $cart['cart_jumlah'];
                    $detail_model->detail_total = $cart['cart_jumlah'] * $menu->menu_harga;
                    $detail_model->detail_tanggal = $cart['cart_tanggal'];
                    $detail_model->detail_status = 'belum dikirim';
                    $detail_model->save();
                }
                
                $provider = Users::find($provider->users_id);
                $provider->notify(new OrderMadeNotif($historyPemesanan));
            }

            // clear cart
            $userCarts = Cart::where("users_customer",$users_customer)->get("cart_id")->map(function ($cartItem, $key) {
                return $cartItem->cart_id;
            });
            Cart::destroy($userCarts->all());
            
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
            'status' => 'created',
            'message' => 'successfully created pemesanan'
        ]);
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
        $pemesanan = HistoryPemesanan::find($id);
        $this->authorize('view',$pemesanan);

        return response()->json([
            'status' => "success",
            'message' => "successfully fetched data",
            'data' => new PemesananResource($pemesanan)
        ],200);
    }

    /**
     * Display pemesanan details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showDelivery(Request $request)
    {
        // authorize
        $this->authorize('viewDelivery',HistoryPemesanan::class);

        $new_detail = new DetailPemesanan();
        $tablename = $new_detail->getTable();
        $columns = Schema::getColumnListing($tablename);
        $validator = Validator::make($request->all(),[
            'sort' => 'nullable',
            'sort.column' => [ 'nullable' , Rule::in($columns)],
            'sort.type' => ['nullable', Rule::in(['asc','desc'])],
            "month" => ["required_with:year","gte:1","lte:12"],
            "year" => ["required_with:month","gt:2010", "lt:3000"],
            "detail_status" => [Rule::in(['belum dikirim','terkirim','diterima'])]
        ]);
        if ($validator->fails()) {
            return response() ->json([
                'status' => 'unprocessable content',
                'message' => 'There are errors found on the data you have entered',
                'errors' => $validator->errors(),
            ],422);
        }

        $sort_column = $request->sort['column'] ?? "detail_tanggal";
        $sort_type = $request->sort['type'] ?? "asc";

        if ($request->has('month') && $request->has('year')) {
            $year = $request->year;
            $month = $request->month;
        } else {
            $year =  date("Y");
            $month =  date("m");
        }

        $user = $request->user();
        $thismonth = DetailPemesanan::whereMonth('detail_tanggal',$month)->whereYear('detail_tanggal',$year)
            ->where(function (Builder $query) {
                $query->where('detail_status',"belum dikirim")
                    ->orWhere('detail_status',"terkirim");
            })
            ->whereRelation("HistoryPemesanan",'pemesanan_status','diterima')
            ->with([
                    'HistoryPemesanan:pemesanan_id,users_customer,users_provider' => [
                        'UsersCustomer:users_id,users_nama,users_alamat,users_telepon'
                    ],
                    'Menu'
                ])
            ;

        // if ($request->has('detail_status') && $request->detail_status != "") {
        //     $thismonth = $thismonth->where('detail_status',$request->detail_status);
        // }
        // return $thismonth->get();

        if ($user->users_role === 'provider') {
            $thismonth = $thismonth->whereRelation('HistoryPemesanan', 'users_provider', $user->users_id);
        } else if ($user->users_role === 'customer') {
            $thismonth = $thismonth->whereRelation('HistoryPemesanan', 'users_customer', $user->users_id);
        } 

        return response()->json([
            'status' => "success",
            'message' => "successfully fetched data",
            'data' => $thismonth->orderBy($sort_column,$sort_type)->get()
        ],200);
    }

    /**
     * accept history pemesanan
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     **/
    public function accept($id)
    {
        // authorize
        $pemesananTerpilih = HistoryPemesanan::findOrFail($id);
        $this->authorize('accept',$pemesananTerpilih);

        $pemesananTerpilih->pemesanan_status = 'diterima';
        $pemesananTerpilih->save();

        return response()->json([
            'status' => "success",
            'message' => "successfully changed pesanan status to delivered",
        ],200);
    }

    /**
     * reject history pemesanan
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     **/
    public function reject($id)
    {
        // authorize
        $pemesananTerpilih = HistoryPemesanan::findOrFail($id);
        $this->authorize('reject',$pemesananTerpilih);

        $pemesananTerpilih->pemesanan_status = 'ditolak';
        $pemesananTerpilih->save();

        return response()->json([
            'status' => "success",
            'message' => "successfully changed pesanan status to delivered",
        ],200);
    }

    /**
     * Set detail pemesanan status to terkirim
     *
     * @param  int  $detail_id
     * @return \Illuminate\Http\Response
     **/
    public function kirim($detail_id)
    {
        // authhorize
        $detailTerpilih = DetailPemesanan::findOrFail($detail_id);
        $headerTerpilih = $detailTerpilih->HistoryPemesanan()->first();
        $this->authorize('kirim',$headerTerpilih);

        $detailTerpilih->detail_status = 'terkirim';
        $detailTerpilih->save();

        return response()->json([
            'status' => "success",
            'message' => "successfully changed pesanan status to delivered",
        ],200);
    }

    /**
     * Set detail pemesanan status to diterima
     *
     * @param  int  $detail_id
     * @return \Illuminate\Http\Response
     **/
    public function terima($detail_id)
    {
        // authorize
        $detailTerpilih = DetailPemesanan::findOrFail($detail_id);
        $headerTerpilih = $detailTerpilih->HistoryPemesanan()->first();
        $this->authorize('terima',$headerTerpilih);

        // checks
        if ($detailTerpilih->detail_status === 'belum dikirim') {
            return response()->json([
                'status' => "bad request",
                'message' => "order has not been delivered yet",
            ],400);
        } elseif ($detailTerpilih->detail_status === 'diterima') {
            return response()->json([
                'status' => "bad request",
                'message' => "order has already been received",
            ],400);
        }

        DB::beginTransaction();
        try {
            $detailTerpilih->detail_status = 'diterima';
            $detailTerpilih->save();

            // check all received
            $is_order_complete = DetailPemesanan::where('pemesanan_id',$headerTerpilih->pemesanan_id)
                ->where('detail_status','!=','diterima')
                ->doesntExist();
            
            if ($is_order_complete) {
                // update status to complete
                $headerTerpilih->pemesanan_status = 'selesai';
                $headerTerpilih->save();

                // transfer money
                $total = $headerTerpilih->pemesanan_total;
                $headerTerpilih->UsersCustomer->users_saldo -= $total;
                $headerTerpilih->UsersProvider->users_saldo += $total;

                $headerTerpilih->UsersCustomer->save();
                $headerTerpilih->UsersProvider->save();
            }

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
            'status' => "success",
            'message' => "successfully changed pesanan status to received",
            'order_complete' => $is_order_complete
        ],200);
    }

    /**
     * Get all pesanan to currently logged on provider
     *
     * @return \Illuminate\Http\Response
     */
    public function getPesananProvider(Request $request)
    {
        $currUser = $request->user();
        if ($currUser->users_role == "provider") {
            $pemesanan = HistoryPemesanan::where("users_provider",$currUser->users_id)->get()->toArray();
            return response()->json([
                "status" => "success",
                "message" => "successfuly fetched pemesanan",
                "data" => $pemesanan
            ],200);
            // return response()->dro('success',200,'successfuly fetched pemesanan',$pemesanan);
            // return response()->caps('success');
        } else {
            return response()->json([
                "status" => "forbidden",
                "message" => "You are not permitted to view this resource",
            ],403);
        }
    }

    /**
     * Get all pesanan to currently logged on customer
     *
     * @return \Illuminate\Http\Response
     */
    public function getPesananCustomer(Request $request)
    {
        $currUser = $request->user();
        if ($currUser->users_role == "customer") {
            $pemesanan = HistoryPemesanan::where("users_customer",$currUser)->get()->toArray();
            return response()->json([
                "status" => "success",
                "message" => "successfuly fetched pemesanan",
                "data" => $pemesanan
            ],200);
        } else {
            return response()->json([
                "status" => "forbidden",
                "message" => "You are not permitted to view this resource",
            ],403);
        }
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
        // TODO update
        $pesanan = HistoryPemesanan::find($id);
        $this->authorize('update',$pesanan);
        // return $request->user()->can('view',HistoryPemesanan::find($id));
        
        $currUser = new Users((Array)json_decode($request->user()));
        $statusList = [
            'belum dikirim',
            'terkirim',
            'diterima'
        ];
        $validator = Validator::make($request->all(),[
            "pemesanan_id" => "prohibited",
            "users_customer" => ["exists:App\Models\Users,users_id"],
            "users_provider" => "nullable | exists:App\Models\Users,users_id",
            "details" => "nullable",
            "details.*.detail_id" => "prohibited",
            "details.*.menu_id" => "nullable | exists:App\Models\Menu,menu_id",
            "details.*.detail_jumlah" => "nullable | integer",
            "details.*.detail_tanggal" => "nullable | date",
            "details.*.detail_status" => [
                Rule::prohibitedIf(!$currUser->isAdministrator()),
                Rule::in($statusList)
            ],
        ]);
        
        if ($validator->fails()) {
            return response() ->json([
                'status' => 'unprocessable content',
                'message' => 'There are errors found on the data you have entered',
                'errors' => $validator->errors(),
            ],422);
        }
        // drop then insert? upsert?
        // check status
    }

    /**
     * Rate pemesanan
     *
     * @param $id Pemesanan id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     **/
    public function rate(Request $request, $id)
    {
        $pemesananTerpilih = HistoryPemesanan::findOrFail($id);
        $this->authorize('rate',$pemesananTerpilih);

        $validator = Validator::make($request->all(),[
            "rating" => ["required", "integer", "gt:0", "lte:10"]
        ]);
        if ($validator->fails()) {
            return response() ->json([
                'status' => 'unprocessable content',
                'message' => 'There are errors found on the data you have entered',
                'errors' => $validator->errors(),
            ],422);
        }

        // all good
        $pemesananTerpilih->pemesanan_rating = $request->rating;
        $pemesananTerpilih->save();

        return response()->json([
            "status" => "success",
            "message" => "successfuly rate pemesanan",
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
        $pesanan = HistoryPemesanan::findOrFail($id);
        $this->authorize('delete',$pesanan);

        DetailPemesanan::where("pemesanan_id",$id)->delete();
        HistoryPemesanan::destroy($id);
        return response()->json([
            'status' => "success",
            'message' => "successfully deleted pesanan"
        ], 200);
    }
}
