<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryLog extends Model
{
    use HasFactory;

    protected $table = "history_log";
    protected $fillable = [
        "log_title",
        "log_desc",
        "log_datetime",
        "users_id"
    ];

    public function users()
    {
        return $this->belongsTo(Users::class, "users_id", "users_id");
    }
}
