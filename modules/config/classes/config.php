<?php

class Config {

    protected static $inst;

    public $readers;

    public $writers;

    public static function inst() {
        if (null == self::$inst) {
            self::$inst = new Config;
        }
        return self::$inst;
    }

    private function __construct() {
        // empty private constructor
    }

    public function get($key) {
        
    }

    public function attach_reader(Config_Reader $reader) {
        $this->readers []= $reader;
    }

    public function attach_writer(Config_Writer $writer) {
        $this->writers []= $writer;
    }

    public function detach_reader(Config_Reader $reader) {
        $key = array_search($reader, $this->readers);
        if (false !== $key) {
            unset($this->readers[$key]);
        } else {
            throw new Config_Exception('this reader was not attached previously');
        }
    }

}
