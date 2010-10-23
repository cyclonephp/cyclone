<?php

class Config {

    protected static $inst;

    public static function inst() {
        if (null == self::$inst) {
            self::$inst = new Config;
        }
        return self::$inst;
    }

    private function __construct() {
        // empty private constructor
    }

    public function get($key) {
        
    }

}
