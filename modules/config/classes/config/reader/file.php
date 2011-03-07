<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package Config
 */
class Config_Reader_File implements Config_Reader {

    public $root_path;

    public function  __construct($root_path = 'config') {
        $this->root_path = $root_path . DIRECTORY_SEPARATOR;
    }

    public function read($key) {
        $segments = explode('.', $key);
        $filename = array_shift($segments);
        $arr = FileSystem::list_files($this->root_path . $filename . '.php', true);

        if (empty($arr))
            return Config::NOT_FOUND;

        $current = &$arr;
        while ( ! empty($segments)) {
            $current = &$current[array_shift($segments)];
        }
        return $current;
    }
    
}
