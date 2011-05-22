<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package Config
 */
class Config {

    const NOT_FOUND = -1;

    protected static $_instances = array();

    public $readers = array();

    public $writers;

    /**
     *
     * @param string $name config setup name
     * @return Config
     */
    public static function inst($name = 'default') {
        if ( ! array_key_exists($name, self::$_instances)) {
            self::$_instances[$name] = new Config;
        }
        return self::$_instances[$name];
    }

    public static function setup($setupfile = 'setup') {
        $reader = new Config_Reader_File;
        foreach ($reader->read($setupfile) as $name => $instance) {
            $cfg = new Config;
            $cfg->readers = &$instance['readers'];
            $cfg->writers = &$instance['writers'];
            self::$_instances [$name]= $cfg;
        }
    }

    private function __construct() {
        // empty private constructor
    }

    public function get($key) {
        foreach ($this->readers as $reader) {
            if (($tmp = $reader->read($key)) !== Config::NOT_FOUND) {
                return $tmp;
            }
        }
        throw new Config_Exception("no value found for key '$key'");
    }

    public function set($key, $val) {
        foreach ($this->writers as $writer) {
            if ($writer->write($key, $val))
                return;
        }
        throw new Config_Exception("none of the config writers were able to write value '$key'");
    }

    public function prepend_reader(Config_Reader $reader) {
        array_unshift($this->readers, $reader);
    }

    public function prepend_writer(Config_Writer $writer) {
        array_unshift($this->writers, $writer);
    }

    public function prepend_mock() {
        $this->prepend_reader(Config_Storage_Mock::inst());
        $this->prepend_writer(Config_Storage_Mock::inst());
    }

    public function attach_reader(Config_Reader $reader) {
        $this->readers []= $reader;
        return $this;
    }

    public function attach_writer(Config_Writer $writer) {
        $this->writers []= $writer;
        return $this;
    }

    public function detach_reader(Config_Reader $reader) {
        $key = array_search($reader, $this->readers);
        if (false !== $key) {
            unset($this->readers[$key]);
            return $this;
        } else {
            throw new Config_Exception('this reader was not attached previously');
        }
    }

    public function detach_writer(Config_Writer $reader) {
        $key = array_search($reader, $this->readers);
        if (false !== $key) {
            unset($this->writers[$key]);
            return $this;
        } else {
            throw new Config_Exception('this reader was not attached previously');
        }
    }

    public static function load() {

    }

}
