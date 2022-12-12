<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryLog extends Model
{
    use HasFactory;

    protected $table = "history_log";
    protected $primaryKey = "log_id";
    protected $guarded = [];
    public $timestamps = false;

    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'log_timestamp' => 'datetime',
    ];

    public function Users()
    {
        return $this->belongsTo(Users::class, "users_id", "users_id");
    }
}
