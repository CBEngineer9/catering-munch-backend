<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Users extends Authenticatable
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "users";
    protected $primaryKey = "users_id";
    protected $guarded = [];

    public function getAuthPassword()
    {
        return $this->users_password;
    }

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
