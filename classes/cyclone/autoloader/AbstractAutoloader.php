<?php

namespace cyclone\autoloader;

use cyclone as cy;

require_once __DIR__ . DIRECTORY_SEPARATOR .  '../Autoloader.php';

abstract class AbstractAutoloader implements cy\Autoloader {

    private static $_pool = array();

    public static function add_autoloader(cy\Autoloader $autoloader) {
        self::$_pool []= $autoloader;
    }

    public static function get_classnames($namespace, $with_subnamespaces = TRUE) {
        $rval = array();
        foreach (self::$_pool as $autoloader) {
            $rval = Arr::merge($rval, $autoloader->list_classes($namespace
                    , $with_subnamespaces));
        }
        return $rval;
    }

    public final function register() {
        $this->_register();
        self::add_autoloader($this);
    }

    protected abstract function _register();

}