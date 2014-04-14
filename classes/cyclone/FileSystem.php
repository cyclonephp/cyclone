<?php

namespace cyclone;

/**
 * Helper class to handle the Cascading File System.
 *
 * @author Lajos Pajger <pajla@cyclonephp.org>
 * @author Bence Eros <crystal@cyclonephp.org>
 * @package cyclone
 */
class FileSystem {

    const LIBRARY_BOOTSTRAP_FILE = 'init.php';

    /**
     * @var FileSystem
     */
    private static $_default_instance = NULL;

    /**
     * @return FileSystem
     * @throws CycloneException
     */
    public static function get_default() {
        if (NULL === self::$_default_instance)
            throw new CycloneException("no default FileSystem instance has been initialized");
        return self::$_default_instance;
    }

    /**
     * Bootstrap method for the CFS.
     *
     * Roots should be <library name> => <library root directory> pairs. Not like
     * Kohana, application and system directories should also be included in the
     * list.
     *
     * If $cache_dir is FALSE, then no file path caching will be done. Otherwise
     * $cache_dir/filepaths.txt will be used. This file should exist and it
     * should be writable.
     *
     * @param $roots array: keys are library names, values are absolute root
     *  paths of the libraries
     * @param $cache_dir boolean|string
     */
    public static function bootstrap($roots, $cache_dir = FALSE) {
        return self::$_default_instance = new FileSystem($roots, $cache_dir);
    }

    public function __construct($roots, $cache_dir = FALSE) {
        $this->_roots = $roots;

        if ($cache_dir) {
            $this->_cache_dir = $cache_dir;
            $this->_path_cache_file = $cache_dir . 'filepaths.txt';
            if (file_exists($this->_path_cache_file)) {
                $this->_abs_file_paths = unserialize(file_get_contents($this->_path_cache_file));
            } else {
                $this->_abs_file_paths = array();
            }
            register_shutdown_function(array($this, 'save_cache'));
        } else {
            $this->_abs_file_paths = array();
        }
    }

    /**
     * Absolute paths to the roots of the CFS.
     *
     * Array keys are library names.
     *
     * @var array
     */
    private $_roots;

    /**
     * Array keys are relative file names in the CFS, values are absolute paths.
     *
     * @var array
     * @usedby FileSystem::find_file()
     */
    private $_abs_file_paths;

    /**
     * Absolute path to file cache
     *
     * @var string
     * @usedby FileSystem::save_cache()
     * @usedby FileSystem::bootstrap()
     */
    private $_path_cache_file;

    /**
     *
     * @var string
     * @usedby FileSystem::bootstrap()
     */
    private $_cache_dir;

    /**
     * The permission to create the cache file with. Override this value to change the
     * permissions to be used when creating the path cache file.
     *
     * @var int
     */
    public $cache_file_umask = 0777;

    /**
     * Set to TRUE by find_file() if self::$_abs_file_paths changed and it
     * should be serialized by save_cache()
     *
     * @var boolean
     */
    private $_cache_invalid;

    public function enable_lib($name, $root_path) {
        if (isset($this->_roots[$name]))
            throw new CycloneException("library '$name' is already enabled", 1);

        $this->_roots[$name] = $root_path;
    }

    /**
     * Returns the names of the enabled libraries.
     *
     * @return array
     */
    public function enabled_libs() {
        return array_keys($this->_roots);
    }

    /**
     * Tries to create the system cache directory.
     *
     * @see FileSystem::$_cache_dir
     * @usedby FileSystem::bootstrap()
     */
    private function create_cache_dir() {
        if ( ! is_writable($this->_cache_dir)) {
            if ( ! file_exists($this->_cache_dir)) {
                if ( ! @mkdir($this->_cache_dir, $this->cache_file_umask, TRUE))
                    throw new CycloneException('failed to create cache directory: '
                            . $this->_cache_dir);
            } else 
                throw new CycloneException($this->_cache_dir . ' is not writable');
        }
    }

    /**
     * Returns the absolute path of a subdirectory in the system cache directory.
     *
     * If the directory doesn't exist then tries to create it, and throws an
     * exception if the creation fails.
     *
     * @param $rel_path string the relative path to the subdirectory in system cache
     * @return string the absolute path of the cache directory
     * @throws CycloneException if failed to create the cache directory
     */
    public function get_cache_dir($rel_path) {
        $candidate = $this->_cache_dir . $rel_path;
        if ( ! is_dir($candidate)) {
            if (file_exists($candidate))
                throw new CycloneException("cache path '$rel_path' exists but not a directory");

            if ( ! @mkdir($candidate, 0755, TRUE))
                throw new CycloneException("failed to create cache directory '$rel_path'");
        }
        return $candidate;
    }

    /**
     * Runs library initialization scripts. It should be put in
     * <library-root>/init.php for each libraries.
     *
     * @usedby index.php
     */
    public function run_init_scripts() {
         foreach ($this->_roots as $library_name => $root_path) {
            if (file_exists($fname =
                    ($root_path . DIRECTORY_SEPARATOR . static::LIBRARY_BOOTSTRAP_FILE))) {
                include $fname;
            }
        }
    }

    /**
     * Called as a shutdown function.
     *
     * Saves the internal absolute file path cache if it's invalid.
     */
    public function save_cache() {
        if ($this->_cache_invalid) {
            file_put_contents($this->_path_cache_file, serialize($this->_abs_file_paths));
        }
    }

    /**
     * Main method for handling the CFS.
     *
     * The method searches for the absolute path of the file given by it's
     * relative file name. It iterates on each library root directories (set up
     * in FileSystem::bootstrap()) and checks if the relative file path exists
     * in the library roots.
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
    public function find_file($rel_filename){
        if (isset($this->_abs_file_paths[$rel_filename])) {
            $candidate = $this->_abs_file_paths[$rel_filename];
            if ( ! is_null($this->_path_cache_file) && ! file_exists($candidate)) {
                unset($this->_abs_file_paths[$rel_filename]);
                $this->_cache_invalid = TRUE;
            } else {
                return $candidate;
            }
        }
        
        foreach ($this->_roots as $root_path) {
            $candidate = $root_path . $rel_filename;
            if (file_exists($candidate)) {
                $this->_cache_invalid = TRUE;
                $this->_abs_file_paths[$rel_filename] = $candidate;
                return $candidate;
            }
        }
        return FALSE;
    }

    public static function explode_dir_file($path) {
        $dir = substr($path, 0, strrpos($path, \DIRECTORY_SEPARATOR));
        $file = substr($path, strrpos($path, \DIRECTORY_SEPARATOR) + 1);
        return array($dir, $file);
    }

    public static function copy($source, $target) {
        if ( ! file_exists($source))
            throw new FileSystemException("file '$source' does not exist'", FileSystemException::FILE_NOT_FOUND);

        if ( ! is_readable($source))
            throw new FileSystemException("file '$source' is not readable'", FileSystemException::FILE_NOT_READABLE);

        list($target_dir, $target_file) = static::explode_dir_file($target);
        if ( ! file_exists($target_dir))
            mkdir($target_dir, 0777, TRUE);

        if ( ! is_writable($target_dir))
            throw new FileSystemException("file '$target' is not writable", FileSystemException::FILE_NOT_WRITABLE);

        copy($source, $target);
    }

    /**
     * If $array_merge is FALSE then collects all the occurences of the relative
     * file path in the library root directories and the return value will be an
     * array of <library-name> => <absolute-path> pairs.
     *
     * If $array_merge is TRUE then it will load the arrays from the found files
     * and it will merge these arrays. The merged array will be the return value.
     *
     * @param string $rel_filename
     * @param boolean $array_merge
     * @return array
     */
    public function list_files($rel_filename, $array_merge = FALSE) {
        $rval = array();
        if ($array_merge) {
            foreach ($this->_roots as $library => $root_path) {
                $candidate = $root_path . $rel_filename;
                if (file_exists($candidate)) {
                    $rval = Arr::merge(require $candidate, $rval);
                }
            }
        } else {
            foreach ($this->_roots as $library => $root_path) {
                $candidate = $root_path . $rel_filename;
                if (file_exists($candidate)) {
                    $rval[$library] = $candidate;
                }
            }
        }
        return $rval;
    }

    public function get_root_path($library) {
        if (isset($this->_roots[$library]))
            return $this->_roots[$library];

        throw new FileSystemException("library '$library' is not installed");
    }

    /**
     * It collects the files found in the CFS root directories + relative
     * directory path.
     *
     * @param string $dir relative path
     * @param array $libraries
     * @return array
     */
    public function list_directory($dir, $libraries = NULL) {
        if (NULL === $libraries) {
            $libraries = array_keys($this->_roots);
        }
        $rval = array();
        foreach ($libraries as $library_name) {
            if ( ! isset($this->_roots[$library_name]))
                throw new CycloneException("library '$library_name' is not installed");
            $root_dir = $this->_roots[$library_name];
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
                            $rval[$rel_path] += $this->list_directory($rel_path);
                        } else {
                            $rval[$rel_path] = $this->list_directory($rel_path);
                        }
                    } elseif ( ! isset($rval[$rel_path])) {
                        $rval[$rel_path] = $abs_path;
                    }
                }
            }
        }
        return $rval;
    }

    /**
     * Removes a directory from the file system, regardless if it's empty or not
     * (so it's equivalent to the unix <code>rm -r</code> command).
     * This helper method has nothing to do with the cascading file system.
     *
     * @param string $abs_path
     * @throws CycloneException if $abs_path is not a directory
     */
    public static function rmdir($abs_path) {
        if ( ! is_dir($abs_path))
            throw new CycloneException("'$abs_path' is not a directory");

        $dir_handle = opendir($abs_path);
        while($file = readdir($dir_handle)) {
            if ($file == '.' || $file == '..')
                continue;
            $file_abs_path = $abs_path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($file_abs_path)) {
                static::rmdir($file_abs_path);
                continue;
            }
            if ( ! @unlink($file_abs_path)) {
                throw new CycloneException("failed to unlink '$file'");
            }
        }
        closedir($dir_handle);
        rmdir($abs_path);
    }

    public static function autoloader_tests($classname) {
        $classname = strtolower($classname);
        $rel_filename = 'tests/' . str_replace('_', DIRECTORY_SEPARATOR, $classname) . '.php';

        $result = FileSystem::get_default()->find_file($rel_filename);
        if ($result) {
            include_once $result;
            return TRUE;
        }
        return FALSE;
    }

    public static function autoloader_camelcase($classname){
        $rel_filename = 'classes/'.str_replace('_', DIRECTORY_SEPARATOR, $classname).'.php';

        $result = FileSystem::get_default()->find_file($rel_filename);
        if($result){
            include_once $result;
            return TRUE;
        }
        return FALSE;
    }

    public static function is_absolute_path($name) {
        return $name{0} === \DIRECTORY_SEPARATOR;
    }

    public static function mktree($dirs, $parent_root = NULL) {
        if (NULL === $parent_root) {
            $parent_root = '.';
        }
        foreach ($dirs as $name => $content) {
            if ( ! self::is_absolute_path($name)) {
                $file_path = $parent_root . \DIRECTORY_SEPARATOR . $name;
            } else {
                $file_path = $name;
            }
            if (is_array($content)) {
                mkdir($file_path);
                static::mktree($content, $file_path);
            }
            else file_put_contents($file_path, $content);
        }
    }

    /**
     * Called by CLI.
     *
     * @param array $args
     */
    public static function init_lib_dirs($args) {
        $root_dir = $args['--directory'];
        $fs_tree = array(
            $root_dir => array(
                'config' => array(
                    'dev' => array(),
                    'test' => array(),
                    'prod' => array()
                ),
                'classes' => array(),
                'views' => array(),
                'tests' => array(),
                'manual' => array()
            )
        );
        try {
            $sys_root = FileSystem::get_default()->get_root_path('cyclone');
        } catch (CycloneException $ex) {
            // maybe the cyclone core is named system
            $sys_root = FileSystem::get_default()->get_root_path('system');
        }
        if ($args['--app']) {
            $app_files = array(
                $root_dir => array(
                    'logs' => array(),
                    '.cache' => array(),
                    'config' => array(
                        'core.php' => file_get_contents($sys_root . 'config/core.php'),
                        'setup.php' => file_get_contents($sys_root . 'config/setup.php')
                    ),
                    'views' => array(
                        'layout.php' => file_get_contents($sys_root . 'views/example.layout.php')
                    )
                )
            );
            $fs_tree = Arr::merge($fs_tree, $app_files);
        }
	    static::mktree($fs_tree);
        if ($args['--app']) {
            chmod(realpath($root_dir) . '/.cache', 0777);
            chmod(realpath($root_dir) . '/logs', 0777);
        }
    }

    public static function copy_dir_contents($src_path, $dst_path, $forced = TRUE) {
        $dir_handle = opendir($src_path);
        if (FALSE === $dir_handle)
            throw new FileSystemException("failed to open directory '$src_path'");

        if ( ! is_dir($dst_path))
            throw new FileSystemException("'$dst_path' is not a directory");

        if ( ! is_writable($dst_path))
            throw new FileSystemException("'$dst_path' is not writable");

        if ($src_path{strlen($src_path) - 1} != \DIRECTORY_SEPARATOR) {
            $src_path .= \DIRECTORY_SEPARATOR;
        }

        if ($dst_path{strlen($dst_path) - 1} != \DIRECTORY_SEPARATOR) {
            $dst_path .= \DIRECTORY_SEPARATOR;
        }

        while( ($filename = readdir($dir_handle)) !== FALSE) {
            if ($filename === '.' || $filename === '..')
                continue;
            $src_file_abs_path = $src_path . $filename;
            $dst_file_abs_path = $dst_path . $filename;
            if (is_dir($src_file_abs_path)) {
                if ( ! is_dir($dst_file_abs_path)) {
                    if (file_exists($dst_file_abs_path)) {
                        if ($forced) {
                            if ( ! @unlink($dst_file_abs_path))
                                throw new FileSystemException("failed to delete '$dst_file_abs_path'");
                        } else {
                            continue;
                        }
                    }
                    if ( ! mkdir($dst_file_abs_path))
                        throw new FileSystemException("failed to create directoty '$dst_file_abs_path'");
                }
                static::copy_dir_contents($src_file_abs_path, $dst_file_abs_path, $forced);
            } else {
                if (file_exists($dst_file_abs_path)) {
                    if ( ! $forced)
                        continue;
                    if ( ! is_writable($dst_file_abs_path))
                        throw new FileSystemException("cannot overwrite '$dst_file_abs_path'");
                }
                if ( ! copy($src_file_abs_path, $dst_file_abs_path))
                    throw new FileSystemException("failed to copy '$src_file_abs_path' to '$dst_file_abs_path'");
            }
        }
        closedir($dir_handle);
    }

    public static function package_example($args) {
        try {
            $src_path = FileSystem::get_default()->get_root_path($args['--src-lib']);
        } catch (CycloneException $ex) {
            echo "error: library '${args['--src-lib']}'' does not exist";
        }

        try {
            $dst_path = FileSystem::get_default()->get_root_path($args['--dst-lib']);
            $dst_path .= 'examples' . \DIRECTORY_SEPARATOR . $args['--name'] . \DIRECTORY_SEPARATOR;
        } catch (CycloneException $ex) {
            echo "error: library ${args['--dst-lib']} does not exist";
        }

        $forced = $args['--forced'];
        $failed = FALSE;
        if ( ! is_dir($dst_path)) {
            if (file_exists($dst_path)) {
                if ($forced) {
                    if ( ! @unlink($dst_path)) {
                        echo "failed to remove file '$dst_path'";
                        $failed = TRUE;
                    }
                } else {
                    echo "error: destination path $dst_path exists and not a directory";
                    $failed = TRUE;
                }
            }
            if ( ! $failed) {
                mkdir($dst_path, 0755, TRUE);
            }
        }
        if ( ! $failed) {
            try {
                static::copy_dir_contents($src_path, $dst_path, $forced);
            } catch (FileSystemException $ex) {
                echo $ex->getMessage() . PHP_EOL;
            }
        }
    }

    public static function get_available_examples() {
        $rval = array();
        $example_dirs = static::list_files('examples');
        foreach ($example_dirs as $lib_name => $dir) {
            $rval[$lib_name] = array();
            $dir_handle = opendir($dir);
            while ( ($subdirname = readdir($dir_handle)) !== FALSE) {
                if ($subdirname === '.' || $subdirname === '..')
                    continue;
                if ( ! is_dir($subdirname))
                    log_warning(get_called_class(), "invalid example: '$subdirname' is not a directory");

                $rval[$lib_name] []= $subdirname;
            }
            closedir($dir_handle);
        }
        return $rval;
    }

    public static function list_examples() {
        foreach (static::get_available_examples() as $lib_name => $examples) {
            foreach ($examples as $example) {
                echo "$lib_name/$example" . PHP_EOL;
            }
        }
    }

    public static function install_example($args) {
        if (strpos($args['--example'], '/') === FALSE) {
            echo "invalid example name: '{$args['--example']}'" . PHP_EOL;
            return;
        }
        list($lib_name, $example_name) = explode('/', $args['--example']);
        $avail_examples = static::get_available_examples();
        if ( ! isset($avail_examples[$lib_name]) || ( ! in_array($example_name, $avail_examples[$lib_name]))) {
            echo "example '{$args['--example']}' does not exist. Failed to install" . PHP_EOL;
            return;
        }

        $src_path = FileSystem::get_default()->get_root_path($lib_name) . 'examples' . DIRECTORY_SEPARATOR . $example_name;

        try {
            $dst_path = FileSystem::get_default()->get_root_path($args['--destination']);
        } catch (FileSystemException $ex) {
            echo "destination library '{$args['--destination']}' does not exist" . PHP_EOL;
            return;
        }

        static::copy_dir_contents($src_path, $dst_path, $args['--forced']);
        $readme_file = $src_path . \DIRECTORY_SEPARATOR . 'README.txt';
        if (file_exists($readme_file) && is_readable($readme_file)) {
            echo file_get_contents($readme_file) . PHP_EOL;
        }
    }

}
