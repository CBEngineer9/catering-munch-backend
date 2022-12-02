<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryPemesanan extends Model
{
    use HasFactory;

    protected $table = "history_pemesanan";
    protected $fillable = [
        "users_provider",
        "users_customer",
        "pemesanan_jumlah",
        "pemesanan_total",
        "pemesanan_tanggal",
    ];

    public function detail_pemesanan()
    {
        return $this->hasMany(DetailPemesanan::class, "pemesanan_id", "pemesanan_id");
    }

    public function UsersProvider()
    {
        return $this->belongsTo(Users::class, "users_provider", "users_id");
    }

    public function UsersCustomer()
    {
        return $this->belongsTo(Users::class, "users_customer", "users_id");
    }
}
