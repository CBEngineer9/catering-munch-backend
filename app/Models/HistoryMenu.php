<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryMenu extends Model
{
    use HasFactory;

    protected $table = "history_menu";
    protected $fillable = [
        "history_menu_action",
        "menu_id"
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class, "menu_id", "menu_id");
    }
}
