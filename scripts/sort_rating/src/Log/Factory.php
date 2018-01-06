<?php

namespace SortRating\Log;

class Factory
{
    private static $_default = \SortRating\Log\DB::class;

    /**
     * @var array of \SortRating\Contracts\Log
     */
    private static $_instance = array();

    /**
     * returns \SortRating\Contracts\Log instance
     * @param $class - concrete class log instance to get,
     * if class not exists, default log istance will be created
     *
     * @return \SortRating\Contracts\Log
     */
    public static function instance($class = null) {
        if (is_null($class) || !class_exists($class)) {
            $key = self::$_default;
        } else {
            $key = $class;
        }

        if (!array_key_exists($key, self::$_instance)) {
            self::$_instance[$key] = new $key();
        }

        return self::$_instance[$key];
    }
    
    private function __construct() {}

    private function __clone(){}
}