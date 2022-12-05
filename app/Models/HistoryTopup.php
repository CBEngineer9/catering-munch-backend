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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function Users()
    {
        return $this->belongsTo(Users::class, "users_id", "users_id");
    }
}
