<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryTopup extends Model
{
    use HasFactory;

    protected $table = "history_topup";
    protected $primaryKey = "topup_id";
    protected $fillable = [
        "topup_nominal",
        "topup_tanggal",
        "topup_response",
        "users_id",
    ];

    public function Users()
    {
        return $this->belongsTo(Users::class, "users_id", "users_id");
    }
}
