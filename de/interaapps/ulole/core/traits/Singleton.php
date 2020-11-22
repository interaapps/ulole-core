<?php
namespace de\interaapps\ulole\core\traits;

trait Singleton {
    private static $instance;
    /**
     * Gives you the instance or creates it if it isn't set yet 
     */
    public static function getInstance($firstParams = []){
        if (self::$instance = null)
            self::$instance = new static(...$firstParams);
        return static::$instance;
    }


    protected static function setInstance($instance) {
        self::$instance = $instance;
    }
}