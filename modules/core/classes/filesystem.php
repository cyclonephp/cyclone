<?php

/**
 * @author Lajos Pajger <pajla@cyclonephp.com>
 * @author Bence Eros <crystal@cyclonephp.com>
 */
class FileSystem {

    private static $_roots;

    private static $_abs_file_paths;

    private static $_path_cache_file;

    private static $_cache_invalid;

    const MODULE_BOOTSTRAP_FILE = 'init.php';

    public static function bootstrap($roots, $cache_dir = FALSE) {
        self::$_roots = $roots;
        
        if ($cache_dir) {
            self::$_path_cache_file = $cache_dir . 'filepaths.txt';
            if ( ! is_writable(self::$_path_cache_file))
                throw new Exception(self::$_path_cache_file . " is not writable");
            self::$_abs_file_paths = unserialize(file_get_contents(self::$_path_cache_file));
            register_shutdown_function(array('FileSystem', 'save_cache'));
        } else {
            self::$_abs_file_paths = array();
        }
    }

    public static function run_init_scripts() {
         foreach (self::$_roots as $module_name => $root_path) {
            if (file_exists($fname =
                    ($root_path . DIRECTORY_SEPARATOR . self::MODULE_BOOTSTRAP_FILE))) {
                include $fname;
            }
        }
    }

    public static function save_cache() {
        if (self::$_cache_invalid) {
            file_put_contents(self::$_path_cache_file, serialize(self::$_abs_file_paths));
        }
    }

    public static function find_file($rel_filename){
        if (isset(self::$_abs_file_paths[$rel_filename]))
            return self::$_abs_file_paths[$rel_filename];
        
        foreach (self::$_roots as $root_path) {
            $candidate = $root_path . $rel_filename;
            if (file_exists($candidate)) {
                self::$_cache_invalid = TRUE;
                self::$_abs_file_paths[$rel_filename] = $candidate;
                return $candidate;
            }
        }
        return FALSE;
    }

    public static function list_files($rel_filename, $array_merge = FALSE) {
        $rval = array();
        if ($array_merge) {
            foreach (self::$_roots as $module => $root_path) {
                $candidate = $root_path . $rel_filename;
                if (file_exists($candidate)) {
                    $rval = Arr::merge($rval, require $candidate);
                }
            }
        } else {
            foreach (self::$_roots as $module => $root_path) {
                $candidate = $root_path . $rel_filename;
                if (file_exists($candidate)) {
                    $rval[$module] = $candidate;
                }
            }
        }
        return $rval;
    }

    public static function autoloader_kohana($classname) {
        $classname = strtolower($classname);
        $rel_filename = 'classes/' . str_replace('_', DIRECTORY_SEPARATOR, $classname) . '.php';

        $result = FileSystem::find_file($rel_filename);
        if ($result) {
            include_once $result;
            return TRUE;
        }
        return FALSE;
    }

    public static function autoloader_camelcase($classname){
        $rel_filename = 'classes/'.str_replace('_', DIRECTORY_SEPARATOR, $classname).'.php';

        $result = FileSystem::find_file($rel_filename);
        if($result){
            include_once $result;
            return TRUE;
        }
        return FALSE;
    }
}
?>
