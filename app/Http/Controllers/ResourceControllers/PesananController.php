<?php

namespace App\Http\Controllers\ResourceControllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\PemesananResource;
use App\Models\DetailPemesanan;
use App\Models\HistoryPemesanan;
use App\Models\Menu;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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
        $this->authorizeResource(HistoryPemesanan::class, 'pesanan');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // TODO pagination
        $currUser = $request->user();
        if ($currUser->users_role == 'admin') {
            $pemesanan = HistoryPemesanan::all()->toArray();
            return response()->json([
                'status' => "success",
                'message' => "successfully fetched all data",
                'data' => $pemesanan
            ],200);
        } else if ($currUser->users_role == "provider") {
            $pemesanan = HistoryPemesanan::where("users_provider",$currUser->users_id)->get()->toArray();
            return response()->json([
                "status" => "success",
                "message" => "successfuly fetched pemesanan provider",
                "data" => $pemesanan
            ],200);
            // return response()->dro('success',200,'successfuly fetched pemesanan',$pemesanan);
            // return response()->caps('success');
        } else if ($currUser->users_role == "customer") {
            $pemesanan = HistoryPemesanan::where("users_customer",$currUser->users_id)->get()->toArray();
            return response()->json([
                "status" => "success",
                "message" => "successfuly fetched pemesanan customer",
                "data" => $pemesanan
            ],200);
        } else {
            return response([
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
        // already authorize

        $currUser = new Users((Array)json_decode($request->user()));
        $statusList = [
            'belum dikirim',
            'terkirim',
            'diterima'
        ];
        // customer tidak pelu give id
        $validator = Validator::make($request->all(),[
            "users_customer" => [Rule::requiredIf($currUser->isAdministrator()), "exists:App\Models\Users,users_id"],
            "users_provider" => "required | exists:App\Models\Users,users_id",
            "details" => "required",
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
                'message' => 'Data error', // FIXME better sentence
                'errors' => $validator->errors(),
            ],422);
        }

        if ($request->user()->isAdministrator()) {
            $users_customer = $request->users_customer;
        } else if ($request->user()->users_role === 'customer') {
            $users_customer = $request->user()->users_id;
        } 

        // all good
        DB::beginTransaction();
        try {
            $details = $request->details;
            $total = 0;
            $historyPemesanan = new HistoryPemesanan();
            $historyPemesanan->users_provider = $request->users_provider;
            $historyPemesanan->users_customer = $users_customer;
            $historyPemesanan->pemesanan_status = 'menunggu';
            $historyPemesanan->pemesanan_jumlah = count($details);
            $historyPemesanan->pemesanan_total = 0;
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
            $historyPemesanan->pemesanan_total = $total;
            $historyPemesanan->save();

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
        $pemesanan = HistoryPemesanan::findOrFail($id);
        $this->authorize('view',$pemesanan);
        return response()->json([
            'status' => "success",
            'message' => "successfully fetched data",
            'data' => new PemesananResource($pemesanan)
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
            return response([
                "status" => "forbidden"
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
            return response([
                "status" => "forbidden"
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
        
        $validator = Validator::make( $request->all(),[
            ""
        ]);
        
        if ($validator->fails()) {
            return response() ->json([
                'status' => 'unprocessable content',
                'message' => 'Data error.', // FIXME better sentence?
                'errors' => $validator->errors(),
            ],422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $pesanan = HistoryPemesanan::find($id);
        $this->authorize('delete',$pesanan);

        DetailPemesanan::where("pemesanan_id",$id)->delete();
        HistoryPemesanan::destroy($id);
        return response()->json([
            'status' => "success",
            'message' => "successfully deleted pesanan"
        ], 200);
    }
}
