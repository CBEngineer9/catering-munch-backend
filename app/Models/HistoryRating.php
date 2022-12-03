<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HistoryRating extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "history_rating";
    protected $primaryKey = "rating_id";
    protected $fillable = [
        "rating_score",
        "menu_id",
        "users_id",
    ];

    public function Menu()
    {
        return $this->belongsTo(Menu::class, "menu_id", "menu_id");
    }

    public function Users()
    {
        return $this->belongsTo(Users::class, "users_id", "users_id");
    }
}
