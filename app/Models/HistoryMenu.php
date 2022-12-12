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
}
