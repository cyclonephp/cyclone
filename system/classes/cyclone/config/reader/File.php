<?php

namespace cyclone\config\reader;

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package cyclone
 */
class File implements \cyclone\config\Reader {

    public $root_path;

    public function  __construct($root_path = 'config') {
        $this->root_path = $root_path . DIRECTORY_SEPARATOR;
    }

    public function read($key) {
        $segments = explode('.', $key);
        $filename = array_shift($segments);
        $arr = \cyclone\FileSystem::list_files($this->root_path . $filename . '.php', true);

        if (empty($arr))
            return \cyclone\Config::NOT_FOUND;

        $current = &$arr;
        while ( ! empty($segments)) {
            $current = &$current[array_shift($segments)];
        }
        return $current;
    }
    
}
