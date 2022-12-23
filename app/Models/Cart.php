<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = "cart";
    protected $primaryKey = "cart_id";
    protected $fillable = [
        "users_customer",
        "menu_id",
        "cart_jumlah",
        "cart_tanggal",
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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function Menu()
    {
        return $this->belongsTo(Menu::class, "menu_id", "menu_id");
    }

    public function Users()
    {
        return $this->belongsTo(Users::class, "users_customer", "users_id");
    }
}
