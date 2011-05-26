<?php

/**
 * Helper class to handle the Cascading File System.
 *
 * @author Lajos Pajger <pajla@cyclonephp.com>
 * @author Bence Eros <crystal@cyclonephp.com>
 */
class FileSystem {

    /**
     * Absolute paths to the roots of the CFS.
     *
     * Array keys are module names.
     *
     * @var array
     */
    private static $_roots;

    /**
     * Array keys are relative file names in the CFS, values are absolute paths.
     *
     * @var array
     * @usedby FileSystem::find_file()
     */
    private static $_abs_file_paths;

    /**
     * Absolute path to file cache
     *
     * @var string
     * @usedby FileSystem::save_cache()
     * @usedby FileSystem::bootstrap()
     */
    private static $_path_cache_file;

    /**
     *
     * @var string
     * @usedby FileSystem::bootstrap()
     */
    private static $_cache_dir;

    /**
     * Set to TRUE by find_file() if self::$_abs_file_paths changed and it
     * should be serialized by save_cache()
     *
     * @var boolean
     */
    private static $_cache_invalid;

    const MODULE_BOOTSTRAP_FILE = 'init.php';

    /**
     * Bootstrap method for the CFS.
     *
     * Roots should be <module name> => <module root directory> pairs. Not like
     * Kohana, application and system directories should also be included in the
     * list.
     *
     * If $cache_dir is FALSE, then no file path caching will be done. Otherwise
     * $cache_dir/filepaths.txt will be used. This file should exist and it
     * should be writable.
     *
     * @param array $roots
     * @param string $cache_dir
     * @usedby index.php
     */
    public static function bootstrap($roots, $cache_dir = FALSE) {
        self::$_roots = $roots;
        
        if ($cache_dir) {
            self::$_cache_dir = $cache_dir;
            self::$_path_cache_file = $cache_dir . 'filepaths.txt';
            if (file_exists(self::$_path_cache_file)) {
                self::$_abs_file_paths = unserialize(file_get_contents(self::$_path_cache_file));
            } else {
                self::$_abs_file_paths = array();
            }
            register_shutdown_function(array('FileSystem', 'save_cache'));
        } else {
            self::$_abs_file_paths = array();
        }
    }

    /**
     * Tries to create the system cache directory.
     *
     * @see FileSystem::$_cache_dir
     * @usedby FileSystem::bootstrap()
     */
    private static function create_cache_dir() {
        if ( ! is_writable(self::$_cache_dir)) {
            if ( ! file_exists(self::$_cache_dir)) {
                if ( ! @mkdir(self::$_cache_dir, 0755, TRUE))
                    throw new Exception('failed to create cache directory: '
                            . self::$_cache_dir);
            } else 
                throw new Exception(self::$_cache_dir . ' is not writable');
        }
    }

    /**
     * Returns the absolute path of a subdirectory in the system cache directory.
     *
     * If the directory doesn't exist then tries to create it, and throws an
     * exception if the creation fails.
     *
     * @param string $rel_path the relative path to the subdirectory in system cache
     * @return string the absolute path of the cache directory
     */
    public static function get_cache_dir($rel_path) {
        $candidate = self::$_cache_dir . $rel_path;
        if ( ! is_dir($candidate)) {
            if (file_exists($candidate))
                throw new Exception("cache path '$rel_path' exists but not a directory");

            if ( ! @mkdir($candidate, 0755, TRUE))
                throw new Exception("failed to create cache directory '$rel_path'");
        }
        return $candidate;
    }

    /**
     * Runs module initialization scripts. It should be put in
     * <module-root>/init.php for each modules.
     *
     * @usedby index.php
     */
    public static function run_init_scripts() {
         foreach (self::$_roots as $module_name => $root_path) {
            if (file_exists($fname =
                    ($root_path . DIRECTORY_SEPARATOR . self::MODULE_BOOTSTRAP_FILE))) {
                include $fname;
            }
        }
    }

    /**
     * Called as a shutdown function.
     *
     * Saves the internal absolute file path cache if it's invalid.
     */
    public static function save_cache() {
        if (self::$_cache_invalid) {
            file_put_contents(self::$_path_cache_file
                    , serialize(self::$_abs_file_paths));
        }
    }

    /**
     * Main method for handling the CFS.
     *
     * The method searches for the absolute path of the file given by it's
     * relative file name. It iterates on each module root directories (set up
     * in FileSystem::bootstrap()) and checks if the relative file path exists
     * in the module roots.
     *
     * If it finds the file then returns the absolute path of the file,
     * otherwise returns FALSE.
     *
     * Not like Kohana::find_file() in this method there is no default file
     * extension and also doesn't do array merging. In the latter case you have
     * to use FileSystem::list_files($rel_filename, TRUE)
     *
     * @param string $rel_filename
     * @return string the absolute file path.
     */
    public static function find_file($rel_filename){
        if (isset(self::$_abs_file_paths[$rel_filename])) {
            $candidate = self::$_abs_file_paths[$rel_filename];
            if ( ! is_null(self::$_path_cache_file) && ! file_exists($candidate)) {
                unset(self::$_abs_file_paths[$rel_filename]);
                self::$_cache_invalid = TRUE;
            } else {
                return $candidate;
            }
        }
        
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

    /**
     * If $array_merge is FALSE then collects all the occurences of the relative
     * file path in the module root directories and the return value will be an
     * array of <module-name> => <absolute-path> pairs.
     *
     * If $array_merge is TRUE then it will load the arrays from the found files
     * and it will merge these arrays. The merged array will be the return value.
     *
     * @param string $rel_filename
     * @param boolean $array_merge
     * @return array
     */
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

    public static function get_root_path($module) {
        if (isset(self::$_roots[$module]))
            return self::$_roots[$module];

        throw new Exception("module '$module' is not installed");
    }

    /**
     * It collects the files found in the CFS root directories + relative
     * directory path.
     *
     * @param string $dir relative path
     * @param array $modules
     * @return array
     */
    public static function list_directory($dir, $modules = NULL) {
        if (NULL === $modules) {
            $modules = array_keys(self::$_roots);
        }
        $rval = array();
        foreach ($modules as $module_name) {
            if ( ! isset(self::$_roots[$module_name]))
                throw new Exception("module '$module_name' is not installed");
            $root_dir = self::$_roots[$module_name];
            $candidate = $root_dir . $dir;
            if (is_dir($candidate)) {
                $handle = opendir($candidate);
                while( ($file = readdir($handle)) !== FALSE) {
                    $abs_path = $candidate . DIRECTORY_SEPARATOR . $file;
                    $rel_path = $dir . DIRECTORY_SEPARATOR . $file;
                    if (is_dir($abs_path)) {
                        if ($file == '.' || $file == '..') 
                            continue;
                        if (isset($rval[$rel_path])) {
                            $rval[$rel_path] += self::list_directory($rel_path);
                        } else {
                            $rval[$rel_path] = self::list_directory($rel_path);
                        }
                    } elseif ( ! isset($rval[$rel_path])) {
                        $rval[$rel_path] = $abs_path;
                    }
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

    public static function autoloader_tests($classname) {
        $classname = strtolower($classname);
        $rel_filename = 'tests/' . str_replace('_', DIRECTORY_SEPARATOR, $classname) . '.php';

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
