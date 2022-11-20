<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryTopup extends Model
{
    use HasFactory;

    protected $table = "history_topup";
    protected $fillable = [
        "topup_nominal",
        "topup_tanggal",
        "topup_response",
        "users_id",
    ];

    public function users()
    {
        return $this->belongsTo(Users::class, "users_id", "users_id");
    }
}
