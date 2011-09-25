<?php

namespace cyclone\config\reader;

use cyclone as cy;

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package Config
 */
class FileEnv implements \cyclone\config\Reader {

    /**
     * @var Config_Readed_Files
     */
    protected $env_reader;

    /**
     * @var Config_Readed_Files
     */
    protected $default_reader;

    public function read($key) {
        $env_val = $this->env_reader->read($key);
        $def_val = $this->default_reader->read($key);
        if (is_array($env_val) && is_array($def_val)) {
            return cy\Arr::merge($def_val, $env_val);
        } else {
            return $env_val === cy\Config::NOT_FOUND ? $def_val : $env_val;
        }
    }

    public function  __construct($root_path = 'config') {
        $this->env_reader = new File($root_path
                .DIRECTORY_SEPARATOR
                . cy\Env::$current);
        $this->default_reader = new File($root_path);
    }

    
}
