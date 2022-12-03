<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPemesanan extends Model
{
    use HasFactory;

    protected $table = "detail_pemesanan";
    protected $primaryKey = "datail_id";
    protected $fillable = [
        "pemesanan_id",
        "menu_id",
        "detail_jumlah",
        "detail_total",
    ];

    public function Menu()
    {
        return $this->belongsTo(Menu::class, "menu_id", "menu_id");
    }

    public function HistoryPemesanan()
    {
        return $this->belongsTo(HistoryPemesanan::class, "pemesanan_id", "pemesanan_id");
    }
}
