<?php
namespace de\interaapps\ulole\core\traits;

trait Singleton {
    private static $instance;
    public static function getInstance(){
        return static::$instance;
    }


    protected static function setInstance($instance)
    {
        self::$instance = $instance;
    }
}