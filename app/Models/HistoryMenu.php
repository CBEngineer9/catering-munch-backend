<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryMenu extends Model
{
    use HasFactory;

    protected $table = "history_menu";
    protected $primaryKey = "history_menu_id";
    protected $fillable = [
        "history_menu_action",
        "menu_id"
    ];

    public function Menu()
    {
        return $this->belongsTo(Menu::class, "menu_id", "menu_id");
    }
}
