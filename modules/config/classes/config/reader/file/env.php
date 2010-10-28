<?php


class Config_Reader_File_Env implements Config_Reader {

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
            return Arr::merge($env_val, $def_val);
        } else {
            return $env_val === false ? $def_val : $env_val;
        }
    }

    public function  __construct($default_path = 'config/all') {
        $this->env_reader = new Config_Reader_File('config'
                .DIRECTORY_SEPARATOR
                .Kohana::$environment);
        $this->default_reader = new Config_Reader_File($default_path);
    }

    
}