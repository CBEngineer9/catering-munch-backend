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

    public function Users()
    {
        return $this->belongsTo(Users::class, "users_id", "users_id");
    }

    public function history_rating()
    {
        return $this->hasMany(HistoryRating::class, "menu_id", "menu_id");
    }

    public function detail_pemesanan()
    {
        return $this->hasMany(DetailPemesanan::class, "menu_id", "menu_id");
    }

    public function history_menu()
    {
        return $this->hasMany(HistoryMenu::class, "menu_id", "menu_id");
    }
}
