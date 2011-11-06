<?php

namespace cyclone\view;

class ViewFactory {

    public static $default = 'cyclone\\view\\PHPView';

    public static function create($name = NULL, $data = NULL, $is_absolute = FALSE) {
        $classname = self::$default;
        return new $classname($name, $data, $is_absolute);
    }

}