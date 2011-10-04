<?php

namespace cyclone\autoloader;

require_once __DIR__ . DIRECTORY_SEPARATOR .  '../Autoloader.php';

class Kohana implements \cyclone\Autoloader {

    private static $_inst;

    /**
     * @return Autloader_Kohana
     */
    public static function inst() {
        if (NULL === self::$_inst) {
            self::$_inst = new Kohana;
        }
        return self::$_inst;
    }


    private function  __construct() {
        // empty private constructor
    }

    public function register() {
        spl_autoload_register(array($this, 'autoload'));
    }

    public function autoload($classname) {
        $classname = strtolower($classname);
        $rel_filename = 'classes/' . str_replace('_', DIRECTORY_SEPARATOR, $classname) . '.php';

        $result = \cyclone\FileSystem::find_file($rel_filename);
        if ($result) {
            include_once $result;
            return TRUE;
        }
        return FALSE;
    }

    public function  list_classes($libs = NULL) {
        ;
    }

    public function  list_testcases($libs = NULL) {
        ;
    }

}