<?php

namespace cyclone\autoloader;
use cyclone as cy;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'AbstractAutoloader.php';

class Kohana extends AbstractAutoloader {

    private static $_inst;

    /**
     * @return cyclone\autoloader\Kohana
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

    protected function _register() {
        spl_autoload_register(array($this, 'autoload'));
    }

    public function autoload($classname) {
        $classname = strtolower($classname);
        $rel_filename = 'classes/' . str_replace('_', DIRECTORY_SEPARATOR, $classname) . '.php';

        $result = \cyclone\FileSystem::get_default()->find_file($rel_filename);
        if ($result) {
            include $result;
            return TRUE;
        }
        return FALSE;
    }

    public function  list_classes($namespace, $with_subnamespaces = TRUE) {
        $rel_path = 'classes' . DIRECTORY_SEPARATOR;
        $rel_path .= str_replace('_', DIRECTORY_SEPARATOR, $namespace);

        $files = cy\FileSystem::get_default()->list_directory($rel_path);
        return $this->extract_classnames($files, $with_subnamespaces);
    }

    private function extract_classnames($dir, $with_subnamespaces) {
        $rval = array();
        foreach ($dir as $rel_path => $file) {
            if (is_array($file)) {
                if ($with_subnamespaces) {
                    $rval = cy\Arr::merge($rval, $this->extract_classnames($file, $with_subnamespaces));
                }
            } else {
                $rval []= $this->extract_classname($rel_path);
            }
        }
        return $rval;
    }

    private function extract_classname($rel_path) {
        $strlen_classes = strlen('classes/');
        $classname = substr($rel_path, $strlen_classes, (strlen($rel_path) - $strlen_classes - strlen('.php')));
        return str_replace(DIRECTORY_SEPARATOR, '_', $classname);
    }

}