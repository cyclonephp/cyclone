<?php

namespace cyclone;

/**
 * @author Bence Eros <crystal@cyclonephp.org>
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
        $reader = new \cyclone\config\reader\FileReader;
        foreach ($reader->read($setupfile) as $name => $instance) {
            $cfg = new Config;
            $cfg->readers = $instance['readers'];
            $cfg->writers = $instance['writers'];
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
        throw new config\Exception("no value found for key '$key'");
    }

    public function set($key, $val) {
        foreach ($this->writers as $writer) {
            if ($writer->write($key, $val))
                return;
        }
        throw new config\Exception("none of the config writers were able to write value '$key'");
    }

    public function prepend_reader(config\Reader $reader) {
        array_unshift($this->readers, $reader);
    }

    public function prepend_writer(config\Writer $writer) {
        array_unshift($this->writers, $writer);
    }

    public function prepend_mock() {
        $this->prepend_reader(config\MockStorage::inst());
        $this->prepend_writer(config\MockStorage::inst());
    }

    public function attach_reader(config\Reader $reader) {
        $this->readers []= $reader;
        return $this;
    }

    public function attach_writer(config\Writer $writer) {
        $this->writers []= $writer;
        return $this;
    }

    public function detach_reader(config\Reader $reader) {
        $key = array_search($reader, $this->readers);
        if (false !== $key) {
            unset($this->readers[$key]);
            return $this;
        } else {
            throw new config\Exception('this reader was not attached previously');
        }
    }

    public function detach_writer(config\Writer $reader) {
        $key = array_search($reader, $this->readers);
        if (false !== $key) {
            unset($this->writers[$key]);
            return $this;
        } else {
            throw new config\Exception('this reader was not attached previously');
        }
    }

    public static function load() {

    }

}
