<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '../autoloader.php';

class Autoloader_Namespaced implements Autoloader {

    private static $_inst;

    public static function inst() {
        if (NULL === self::$_inst) {
            self::$_inst = new Autoloader_Namespaced;
        }
        return self::$_inst;
    }

    public function register() {
        spl_autoload_register(array($this, 'autoload'));
    }

    public function autoload($classname) {
        $classname = strtolower($classname);
        $rel_filename = 'classes/' . str_replace('\\', DIRECTORY_SEPARATOR, $classname) . '.php';

        $result = FileSystem::find_file($rel_filename);
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