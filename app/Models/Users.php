<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Users extends Authenticatable
{
    use HasFactory;
    use SoftDeletes;
    use HasApiTokens;

    protected $table = "users";
    protected $primaryKey = "users_id";
    protected $guarded = [];

     /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'users_password',
    ];

    public function getAuthPassword()
    {
        return $this->users_password;
    }

    public function Menu()
    {
        return $this->hasMany(Menu::class, "users_id", "users_id");
    }

    public function HistoryRating()
    {
        return $this->hasMany(HistoryRating::class, "users_id", "users_id");
    }

    public function HistoryLog()
    {
        return $this->hasMany(HistoryLog::class, "users_id", "users_id");
    }

    public function HistoryTopup()
    {
        return $this->hasMany(HistoryTopup::class, "users_id", "users_id");
    }

    public function HistoryPemesananProvider()
    {
        return $this->hasMany(HistoryPemesanan::class, 'users_provider', 'users_id');
    }

    public function HistoryPemesananCustomer()
    {
        return $this->hasMany(HistoryPemesanan::class, "users_customer", "users_id");
    }
}
