<?php

namespace DB\SQL;

/** 
 * Rails Type Logging with color in terminal (tail -f sql.log).
 * To use enable or disable
 * DB\SQL::$log_sql_enabled = $_ENV['ENVIRONMENT'] != 'production' ? true : false;
 * By defaul sets to sql.log or you can set your own log file
 * DB\SQL::$logger = new \Log("sql.log");
**/
trait Logger {

    public static $log_line_max_length = 50000;
    public static $log_color = 35;
    public static $log_sql_enabled = false;
    public static $logger = null;
    public $model_class = null;

    protected function log_query($query, $duration = null, $name = null) {
        if(self::$log_sql_enabled && $query) {
            self::$log_color = self::$log_color == 35 ? 36 : 35;
            $color = self::$log_color;
            $bold = $color == 36 ? "\e[1m" : '';
            $duration = is_numeric($duration) ? " (".round($duration, 1)."ms)" : '';
            $name = $name ? $name : $this->model_class." Load";
            self::_log("\e[{$color}m{$name}{$duration}\e[0m {$bold}".$query."\e[0m");
        }
    }

    protected static function _log($message) {
        if(self::$log_sql_enabled && $message) {
            if(strlen($message) > self::$log_line_max_length) {
                $message = substr($message, 0, self::$log_line_max_length)."...";
            }
            if(!self::$logger) {
                self::$logger = new \Log("sql.log");
            }
            if(is_object(self::$logger)) {
                self::$logger->write($message);
            } else {
                error_log($message);
            }
        }
    }    

}