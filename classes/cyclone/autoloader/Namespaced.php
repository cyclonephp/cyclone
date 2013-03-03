<?php

namespace cyclone\autoloader;

use cyclone as cy;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'AbstractAutoloader.php';

class Namespaced extends AbstractAutoloader {

    private static $_inst;

    /**
     * @return cyclone\autoloader\Namespaced
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

    protected function _register() {
        spl_autoload_register(array($this, 'autoload'));
    }

    public function autoload($classname) {
        $rel_filename = 'classes/' . str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $classname) . '.php';
        $result = cy\FileSystem::find_file($rel_filename);
        if ($result) {
            include $result;
            return TRUE;
        }
        return FALSE;
    }

    public function  list_classes($namespace, $with_subnamespaces = TRUE) {
        $rel_path = 'classes';
        if (strlen($namespace) == 0 || $namespace{0} != '\\') {
            $rel_path .= DIRECTORY_SEPARATOR;
        }

        $rel_path .= str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
        
        $files = cy\FileSystem::list_directory($rel_path);
        return $this->extract_classnames($files, $with_subnamespaces);
    }

    private function extract_classnames($dir, $with_subnamespaces) {
        $rval = array();
        foreach ($dir as $rel_path => $file) {
            if (is_array($file)) {
                if ($with_subnamespaces) {
                    $rval = cy\Arr::merge($rval, $this->extract_classnames($file, TRUE));
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
        return str_replace(DIRECTORY_SEPARATOR, '\\', $classname);
    }

}