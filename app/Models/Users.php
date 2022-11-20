<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Users extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "users";
    protected $fillable = [
        "users_email",
        "users_telepon",
        "users_nama",
        "users_alamat",
        "users_password",
        "users_saldo",
        "users_role",
        "users_status",
    ];

    public function menu()
    {
        return $this->hasMany(Menu::class, "users_id", "users_id");
    }

    public function history_rating()
    {
        return $this->hasMany(HistoryRating::class, "users_id", "users_id");
    }

    public function history_log()
    {
        return $this->hasMany(HistoryLog::class, "users_id", "users_id");
    }

    public function history_topup()
    {
        return $this->hasMany(HistoryTopup::class, "users_id", "users_id");
    }

    public function history_pemesanan_provider()
    {
        return $this->hasMany(HistoryPemesanan::class, 'users_provider', 'users_id');
    }

    public function history_pemesanan_customer()
    {
        return $this->hasMany(HistoryPemesanan::class, "users_customer", "users_id");
    }
}
