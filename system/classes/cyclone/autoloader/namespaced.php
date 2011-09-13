<?php

namespace cyclone\autoloader;

use cyclone as cy;

require_once __DIR__ . DIRECTORY_SEPARATOR . '../autoloader.php';

class Namespaced implements cy\Autoloader {

    private static $_inst;

    /**
     * @return Autoloader_Namespaced
     */
    public static function inst() {
        if (NULL === self::$_inst) {
            self::$_inst = new Namespaced;
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
        $rel_filename = 'classes/' . str_replace('\\', DIRECTORY_SEPARATOR, $classname) . '.php';

        $result = cy\FileSystem::find_file($rel_filename);
        if ($result) {
            require_once $result;
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