<?php

namespace cyclone\config\reader;

/**
 * @author Bence Eros <crystal@cyclonephp.org>
 * @package cyclone
 */
class FileReader implements \cyclone\config\Reader {

    public $root_path;

    protected $_loaded_files = array();

    public function  __construct($root_path = 'config') {
        $this->root_path = $root_path . DIRECTORY_SEPARATOR;
    }

    public function read($key) {
        $segments = explode('.', $key);
        $filename = array_shift($segments);
        
        if ( ! isset($this->_loaded_files[$filename])) {
            $this->_loaded_files[$filename] =
                    \cyclone\FileSystem::list_files($this->root_path . $filename . '.php', true);
        }
        $arr = $this->_loaded_files[$filename];

        if (empty($arr))
            return \cyclone\Config::NOT_FOUND;

        $current = &$arr;
        while ( ! empty($segments)) {
            $segment = array_shift($segments);
            if ( ! isset($current[$segment]))
                return \cyclone\Config::NOT_FOUND;
            $current = &$current[$segment];
        }
        return $current;
    }
    
}
