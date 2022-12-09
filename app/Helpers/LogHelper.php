<?php
namespace App\Helpers;

use App\Models\HistoryLog;
use Illuminate\Support\Facades\Log;

class LogHelper {
    /**
     * Insert log into database and laravel log
     *
     * @param String $level log level
     * @param String $title log title
     * @param String $desc log description
     * @param Int $userId users id
     * @return type
     * @throws conditon
     **/
    public static function log($level, $title, $desc, $userid)
    {
        
        HistoryLog::insert([
            "log_title" => $title,
            "log_desc" => $desc,
            "users_id" => $userid
        ]);

        if ( $level !== 'debug' || $level !== 'info' || $level !== 'notice' 
            || $level !== 'warning' || $level !== 'error' || $level !== 'critical' 
            || $level !== 'alert' || $level !== 'emergency' 
        ) {
            $level == 'warning';
        }

        $message = "[" . date("Y-M-d h:i:s") . "] " . $title;
        Log::$level(); // TODO
    }
}