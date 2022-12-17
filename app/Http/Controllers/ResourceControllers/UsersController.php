<?php

namespace App\Http\Controllers\ResourceControllers;

use App\Http\Controllers\Controller;
use App\Models\DetailPemesanan;
use App\Models\HistoryTopup;
use App\Models\Users;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // authorize
        $this->authorize('viewAny',Users::class);
        $currUser = $request->user();

        $new_user = new Users();
        $tablename = $new_user->getTable();
        $columns = Schema::getColumnListing($tablename);
        $appended = $new_user->getAppends();
        $columns = array_merge($columns,$appended); // include appends in sortables

        $request->validate([
            'sort' => 'nullable',
            'sort.column' => [ 'required_with:sort.type' , Rule::in($columns)],
            'sort.type' => ['required_with:sort.column', Rule::in(['asc','desc'])],
            'batch_size' => ["integer", "gt:0"],
            "users_role" => [
                'nullable', 
                Rule::prohibitedIf($request->user()->users_role !== 'admin'), 
                Rule::in(['admin','customer','provider'])
            ],
            "users_status" => [
                'nullable',
                Rule::prohibitedIf($request->user()->users_role !== 'admin'), 
                Rule::in(['banned', 'aktif', 'menunggu'])
            ], 
            "users_nama" => ['nullable', "string"],
            "customer_filter" => [
                Rule::prohibitedIf($request->user()->users_role !== 'customer'), 
                Rule::in(['pernah dipesan','sedang dipesan'])
            ]
        ]);

        $sort_column = $request->sort['column'] ?? "users_id";
        $sort_type = $request->sort['type'] ?? "asc";
        $batch_size = $request->batch_size ?? 10;

        $isAppend = false;
        foreach ($appended as $app) {
            if ($sort_column === $app) {
                $isAppend = true;
            }
        }

        $listUser = Users::withTrashed($currUser->users_role === 'admin'); // if admin, with trash
        if (!$isAppend) {
            $listUser = $listUser->orderBy($sort_column,$sort_type);
        }

        if ($currUser->users_role !== 'admin') { // commoners can only see provider
            $listUser = $listUser->where('users_role','provider');
        } elseif ($request->has('users_role')) {
            $listUser = $listUser->where('users_role',$request->users_role);
        } 

        // customer filter (pernah dipesan, sedang dipesan)
        // pernah dipesan = provider dengan pemesanan status selesai
        // sedang dipesan = provider dengan pemesanan status belum selesai
        if ($request->customer_filter === 'pernah dipesan') {
            $listUser = $listUser->whereRelation("HistoryPemesananProvider","users_customer",$currUser->users_id)
                ->whereRelation("HistoryPemesananProvider","pemesanan_status","selesai");
        } elseif ($request->customer_filter === 'sedang dipesan') {
            $listUser = $listUser->whereRelation("HistoryPemesananProvider","users_customer",$currUser->users_id)
                ->whereRelation("HistoryPemesananProvider","pemesanan_status","!=","selesai");
        }

        if ($currUser->users_role !== 'admin') { // default only see aktif, unless ADMIN (sees everything)
            $listUser = $listUser->where('users_status','aktif');
        } elseif ($request->has('users_status')) {
            $listUser = $listUser->where('users_status',$request->users_status);
        }

        if ($request->has('users_nama')) {
            $listUser = $listUser->where('users_nama','like','%'.$request->users_nama.'%');
        }

        // appended column tidak ada di database, harus get dulu baru sort. harus paginate sendiri
        if ($isAppend) {
            $listUser = $listUser->get()->sortBy(function($users) use ($sort_column){
                return $users->$sort_column;
            }, SORT_REGULAR, $sort_type === "desc");

            $batch = LengthAwarePaginator::resolveCurrentPage('page');
            $paginated = new LengthAwarePaginator(
                $listUser->forPage($batch, $batch_size), 
                $listUser->count(), 
                $batch_size, 
                $batch,[
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => 'page'
                ]
            );
        } else {
            $paginated = $listUser->paginate($batch_size);
        }

        return response()->json([
            "status" => "success",
            "message" => "successfully fetched all user",
            "data" => $paginated
        ],200);
    }

    /**
     * Displays all customer
     *
     * @deprecated This method is no longer acceptable get all customer. Use fetch with request body instead.
     * @return \Illuminate\Http\Response
     **/
    public function getAllCustomers()
    {
        $listUser = Users::withTrashed()->where("users_role","customer")->get()->all();
        return response()->json([
            "status" => "success",
            "data" => $listUser
        ],200);
    }

    /**
     * Displays all provider
     *
     * @return \Illuminate\Http\Response
     **/
    public function getAllProviders(Request $request)
    {
        $item = new Users();
        $tablename = $item->getTable();
        $columns = Schema::getColumnListing($tablename);
        // return response()->json($columns,200);
        $request->validate([
            'sort' => 'nullable',
            'sort.column' => [Rule::in($columns)],
            'sort.type' => [Rule::in(['asc','desc'])],
            'batch_size' => "integer|gt:0",
            'batch' => "integer|gt:0",
        ]);
        $listUser = Users::withTrashed()->where("users_role","provider")->get()->all();
        return response()->json([
            "status" => 'success',
            "data" => $listUser
        ],200);
    }

    /**
     * Get full profile
     *
     * @return \Illuminate\Http\Response
     **/
    public function getProfile(Request $request) {
        return response()->json([
            "status" => 'success',
            'message' => 'successfully fetched current user',
            "data" => $request->user(),
        ],200);
    }

    /**
     * Get mini profile
     *
     * @return \Illuminate\Http\Response
     **/
    public function getProfileMini(Request $request) {
        // $user = new Users((Array)json_decode($request->user()));
        $user = $request->user();
        return response()->json([
            "status" => 'success',
            'message' => 'successfully fetched current user',
            "data" => [
                "users_id" => $user->users_id,
                "users_nama" => $user->users_nama,
                "users_role" => $user->users_role,
                "users_saldo" => $user->users_saldo,
            ],
        ],200);
    }
    
    /**
     * Customer topup self e-money
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     **/
    public function topup(Request $request)
    {
        // No authorize, because self edit
        // TODO authorize only customer?
        
        $validator = Validator::make($request->all(),[
            "password" => "required|current_password:web",
            "topup_amount" => "required|integer|max_digits:8",
            // "token_id" => "required", // TODO
        ]);
        if ($validator->fails()) {
            return response() ->json([
                'status' => 'unprocessable content',
                'message' => 'There are errors found on the data you have entered',
                'errors' => $validator->errors(),
            ],422);
        }

        //all good
        $user = Users::find($request->user()->users_id);
        $user->users_saldo += $request->topup_amount;
        $user->save();

        $hist_topup =  new HistoryTopup();
        $hist_topup->topup_nominal = $request->topup_amount;
        $hist_topup->topup_tanggal = now();
        $hist_topup->topup_response = "success";
        $hist_topup->topup_response_code = 200;
        $hist_topup->users_id = $user->users_id;
        $hist_topup->save();

        // $auth_string = base64_encode(env('MIDTRANS_SERVER_KEY').":");

        // $midtrans_response = Http::withHeaders([
        //     'Accept' => 'application/json',
        //     'Content-Type' => 'application/json',
        //     'Authorization' => "basic $auth_string"
        // ])->post('https://api.sandbox.midtrans.com/v2/charge', [
        //     "payment_type" => "credit_card",
        //     "transaction_details"=> [
        //         "order_id" => "$hist_topup->topup_id",
        //         "gross_amount"=> $request->topup_amount
        //     ],
        //     "credit_card" => [
        //         "token_id" => "$request->token_id",
        //         "authentication"=> true,
        //     ],
        //     "customer_details" => [
        //         "nama" => $user->user_nama,
        //         "email" => $user->user_email,
        //         "phone" => $user->user_telepon,
        //     ]
        // ]);

        // $hist_topup->topup_response = $midtrans_response->status_code;
        // $hist_topup->save();

        // // TODO error handling
        
        return response()->json([
            "status" => "success",
            "message" => "successfully top up",
            "data"  => [
                "topup_amount" => $request->topup_amount,
                "users_saldo" => $user->users_saldo,
                // "midtrans_response" => $midtrans_response
            ]
        ]);
    }

    /**
     * Midtrans topup callback
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     **/
    public function topupCallback(Request $request)
    {
        
    }

    /**
     * Get user status. Only supported for admin and provider
     *
     * @return \Illuminate\Http\Response
     **/
    public function getStatus(Request $request) {
        $user = $request->user();
        if ($user->users_role === 'admin') {
            $customer = Users::where('users_role','customer')->count();
            $provider = Users::where('users_role','provider')->where('users_status','!=','menunggu')->count();
            $unverified = Users::where('users_role','provider')->where('users_status','menunggu')->count();
    
            return response()->json([
                "status" => 'success',
                'message' => 'successfully fetched admin stat',
                "data" => [
                    "customers_count" => $customer,
                    "providers_count" => $provider,
                    "unverified_count" => $unverified,
                ],
            ],200);
        } elseif ($user->users_role === 'provider') {
            // $header_pemesanan = Users::find($user->users_id)->HistoryPemesananProvider();
            // $thismonth_delivery = Users::find($user->users_id)->HistoryPemesananProvider()
            //     ->where('pemesanan_status','diterima')
            //     ->with('DetailPemesanan')->get();
            $thismonth_delivery = DetailPemesanan::whereHas('HistoryPemesanan', function(Builder $query) use ($user) {
                    $query->whereRelation('UsersProvider','users_id',$user->users_id);
                })
                ->whereRelation('HistoryPemesanan','pemesanan_status','diterima')
                ->count();
            $totalpendapatan = Users::find($user->users_id)->HistoryPemesananProvider()
                ->whereDoesntHave('DetailPemesanan',function(Builder $query){
                    $query->where('detail_status','belum dikirim')
                        ->orWhere('detail_status','terkirim');
                })
                ->sum('pemesanan_total');
            // return $totalpendapatan;
            // return $header_pemesanan->get();
            // $made_delivery = Users::find($user->users_id)->HistoryPemesananProvider()
            //     ->where('pemesanan_status','diterima')
            //     ->withCount(['DetailPemesanan' => function(Builder $query) {
            //         $query->where('detail_status','terkirim');
            //     }])
            //     ->get();
            $made_delivery = DetailPemesanan::whereHas('HistoryPemesanan', function(Builder $query) use ($user) {
                    $query->whereRelation('UsersProvider','users_id',$user->users_id);
                })
                ->whereRelation('HistoryPemesanan','pemesanan_status','diterima')
                ->where("detail_status",'!=','belum dikirim')
                ->count();
            return response()->json([
                "status" => 'success',
                'message' => 'successfully fetched provider stat',
                "data" => [
                    "thismonth_delivery" => $thismonth_delivery,
                    "total_pendapatan" => $totalpendapatan,
                    "made_delivery" => $made_delivery,
                ],
            ],200);
        }
    }

    /**
     * Approve provider
     *
     * @param $id
     * @return \Illuminate\Http\Response
     **/
    public function approveProvider($id)
    {
        $userTerpilih = Users::findOrFail($id);
        $this->authorize('approve',$userTerpilih);

        $userTerpilih->users_status = "aktif";
        $userTerpilih->save();
        return response()->json([
            'status' => 'updated',
            "message" => "successfully approved provider"
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
        $this->authorize('create',Users::class);
        
        $request->validate([
            "users_nama" => "required",
            "users_email" => "required | email | unique:users,users_email",
            "users_password" => "required | confirmed",
            "users_alamat" => "required",
            "users_telepon" => "required | numeric | digits_between:8,12 | unique:users,users_telepon",
            "users_role" => ["required", Rule::in(["customer","provider"])],
        ]);

        // all good
        Users::create([
            "users_nama" => $request->users_nama,
            "users_email" => $request->users_email,
            "users_password" => $request->users_password,
            "users_alamat" => $request->users_alamat,
            "users_telepon" => $request->users_telepon,
            "users_role" => $request->users_role,
            "users_status" => $request->users_role == "customer" ? "aktif" : "menunggu"
        ]);

        return response()->json([
            "status" => "created",
            'message' => "successfully created user"
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
        $user = Users::withTrashed()->findOrFail($id);
        $this->authorize('view',$user);

        return response()->json([
            "status" => "success",
            "message" => "successfully fetched user",
            "data" => $user
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
        $userTerpilih = Users::findOrFail($id);
        $this->authorize('update',$userTerpilih);
        
        $validator = Validator::make($request->all(),[
            "users_nama" => "nullable|string|max:255",
            "users_email" => "nullable | email | unique:users,users_email|max:255",
            "users_password" => "nullable | confirmed|max:255",
            "users_alamat" => "nullable|max:255",
            "users_telepon" => "nullable | numeric | digits_between:8,12 | unique:users,users_telepon",
            "users_role" => ["nullable", Rule::prohibitedIf($request->user()->users_role !== "admin"), Rule::in(["customer","provider"])],
            "users_desc" => ["nullable", "string", "max:255"],
            "users_saldo" => [Rule::prohibitedIf($request->user()->users_role !== "admin"), "integer", "max_digits:8"]
        ]);
        if ($validator->fails()) {
            return response() ->json([
                'status' => 'unprocessable content',
                'message' => 'There are errors found on the data you have entered',
                'errors' => $validator->errors(),
            ],422);
        }

        // all good
        $columns = $userTerpilih->getFillable();
        foreach ($columns as $column) {
            if ($request->has($column)) {
                $userTerpilih->$column = $request->$column;
            }
        }
        $userTerpilih->save();
        return response()->json([
            'status' => 'created',
            'message' => "succesfully updated user"
        ],201);
    }

    /**
     * Ban the user
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function banUser($id)
    {
        $userTerpilih = Users::findOrFail($id);
        $this->authorize('ban',$userTerpilih);

        if ($userTerpilih->users_status == 'banned') {
            return response()->json([
                'status' => "bad request",
                'message' => "user already banned"
            ],400);
        }
        $userTerpilih->users_status = 'banned';
        $userTerpilih->save();
        return response()->json([
            'status' => "success",
            'message' => "successfuly banned user"
        ],200);
    }

    /**
     * Unban the user
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function unbanUser($id)
    {
        $userTerpilih = Users::findOrFail($id);
        $this->authorize('unban',$userTerpilih);

        if ($userTerpilih->users_status == 'aktif') {
            return response()->json([
                'status' => "bad request",
                'message' => "user is not banned"
            ],400);
        }
        $userTerpilih->users_status = 'aktif';
        $userTerpilih->save();
        return response()->json([
            'status' => "success",
            'message' => "successfuly unban user"
        ],200);
    }

    /**
     * Soft Deletes the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Users::findOrFail($id);
        $this->authorize('delete',$user);

        Users::destroy($id);
        return response()->json([
            'status' => "success",
            'message' => "successfuly soft delete user"
        ],200);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        $user = Users::withTrashed()->findOrFail($id);
        $this->authorize('restore',$user);

        $user->restore();
        return response()->json([
            'status' => "success",
            'message' => "successfuly restore user"
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function purge($id)
    {
        $user = Users::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete',$user);
        
        $user->forceDelete();
        return response()->json([
            'status' => "success",
            'message' => "successfuly delete user"
        ],200);
    }
}
