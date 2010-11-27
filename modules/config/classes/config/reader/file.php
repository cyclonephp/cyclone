<?php

class Config_Reader_File implements Config_Reader {

    public $root_path;

    public function  __construct($root_path = 'config') {
        $this->root_path = $root_path;
    }

    public function read($key) {
        $segments = explode('.', $key);
        $filename = array_shift($segments);
        $files = Kohana::find_file($this->root_path, $filename, null, true);
        if (empty($files))
            return Config::NOT_FOUND;
        $merged = array();
        foreach ($files as $file) {
            $merged = Arr::merge($merged, require $file);
        }
        $current = &$merged;
        while ( ! empty($segments)) {
            $current = &$current[array_shift($segments)];
        }
        return $current;
    }
    
}