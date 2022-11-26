<?php

namespace App\Http\Controllers;

use App\Models\HistoryLog;
use App\Models\Users;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function approve(Request $request)
    {
        $result = Users::find($request->id)->update([
            "users_status" => "aktif",
        ]);

        if ($result) {
            return back()->with("message", "Berhasil approve!");
        }
        return back()->with("message", "Gagal approve!");
    }

    public function ban(Request $request)
    {
        $user = Users::find($request->id);
        if ($request->ban) {
            $result = $user->update([
                "users_status" => "banned",
            ]);
            HistoryLog::create([
                "log_title" => "Admin melakukan banned terhadap " . $user->users_nama,
                "log_desc" => $user->users_nama . " melakukan pelanggaran terhadap aturan!",
                "log_datetime" => now(),
                "users_id" => $user->users_id,
            ]);
        } else {
            $result = $user->update([
                "users_status" => "aktif",
            ]);
            HistoryLog::create([
                "log_title" => "Admin melakukan unban terhadap " . $user->users_nama,
                "log_desc" => "Akun " . $user->users_nama . " sudah aktif kembali!",
                "log_datetime" => now(),
                "users_id" => $user->users_id,
            ]);
        }

        if ($result) {
            return back()->with("message", "Berhasil melakukan ban/unban!");
        }
        return back()->with("message", "Gagal melakukan ban/unban!");
    }
}
