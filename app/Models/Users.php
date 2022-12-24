<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Users extends Authenticatable
{
    use HasFactory, SoftDeletes, HasApiTokens, Notifiable;

    protected $table = "users";
    protected $primaryKey = "users_id";
    protected $fillable = [
        'users_email',
        'users_telepon',
        'users_nama',
        'users_alamat',
        'users_password',
        'users_desc',
        'users_saldo',
        'users_role',
        'users_status'
    ];
    protected $appends = [
        'users_rating',
        'users_photo'
    ];

     /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'users_password',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function usersRating(): Attribute
    {
        return new Attribute(
            get: function() {
                if ($this->users_role !== 'provider') {
                    return null;
                } else {
                    return HistoryPemesanan::where('users_provider',$this->users_id)->average('pemesanan_rating');
                }
            }
        );
    }

    protected function usersPhoto(): Attribute
    {
        return new Attribute(
            get: function() {
                if ($this->users_role !== 'provider') {
                    return null;
                } else {
                    return $this->Menu->first()->menu_photo ?? 'sampleFood.jpeg';
                }
            }
        );
    }

    // public function getUsersRatingAttribute()
    // {
    //     return HistoryPemesanan::where('users_provider',$this->users_id)->average('pemesanan_rating');
    // }

    public function getAuthPassword()
    {
        return $this->users_password;
    }

    public function isAdministrator()
    {
        return $this->users_role === 'admin';
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

    public function CartCustomer()
    {
        return $this->hasMany(Cart::class, "users_customer", "users_id");
    }

    public function CartProvider()
    {
        return $this->hasMany(Cart::class, "users_provider", "users_id");
    }
}
