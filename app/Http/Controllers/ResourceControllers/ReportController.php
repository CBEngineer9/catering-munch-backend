<?php

namespace App\Http\Controllers\ResourceControllers;

use App\Http\Controllers\Controller;
use App\Exports\PenjualanTerbanyakReport;
use App\Helpers\LogHelper;
use App\Models\Users;
use App\Rules\UserRoleRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function penjualanTerbanyak(Request $request) 
    {
        $currUser = new Users((Array)json_decode($request->user()));
        $users_id = $request->user()->users_id;

        $validator = Validator::make($request->all(),[
            "provider_id" => [
                Rule::prohibitedIf(!$currUser->isAdministrator()),  
                "exists:App\Models\Users,users_id", 
                new UserRoleRule("provider")
            ]
        ]);
        if ($validator->fails()) {
            return response() ->json([
                'status' => 'unprocessable content',
                'message' => 'There are errors found on the data you have entered',
                'errors' => $validator->errors(),
            ],422);
        }
        
        if ($currUser->users_role === 'admin') {
            $user_id = $request->provider_id;
        } elseif ($currUser->users_role === 'provider') {
            $user_id = $request->user()->users_id;
        }
        
        LogHelper::log('info','Report download', "Penjualan terbanyak report downloaded", $users_id);

        return Excel::download(new PenjualanTerbanyakReport($user_id), 'penjualanTerbanyak.xlsx');
    }
}
