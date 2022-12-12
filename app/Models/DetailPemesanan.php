<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPemesanan extends Model
{
    use HasFactory;

    protected $table = "detail_pemesanan";
    protected $primaryKey = "detail_id";
    protected $fillable = [
        "pemesanan_id",
        "menu_id",
        "detail_jumlah",
        "detail_total",
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

    public function HistoryPemesanan()
    {
        return $this->belongsTo(HistoryPemesanan::class, "pemesanan_id", "pemesanan_id");
    }
}
