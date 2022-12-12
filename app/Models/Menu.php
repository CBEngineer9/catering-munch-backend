<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "menu";
    protected $primaryKey = "menu_id";
    protected $fillable = [
        "menu_nama",
        "menu_foto",
        "menu_harga",
        "menu_tanggal",
        "menu_rating",
        "menu_status",
        "users_id",
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function Users()
    {
        return $this->belongsTo(Users::class, "users_id", "users_id");
    }

    public function history_rating()
    {
        return $this->hasMany(HistoryRating::class, "menu_id", "menu_id");
    }

    public function DetailPemesanan()
    {
        return $this->hasMany(DetailPemesanan::class, "menu_id", "menu_id");
    }

    public function HistoryMenu()
    {
        return $this->hasMany(HistoryMenu::class, "menu_id", "menu_id");
    }
}
