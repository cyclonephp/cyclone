<?php

namespace cyclone\config;

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package cyclone
 */
class MockStorage implements Reader, Writer {

    private static $_inst;

    /**
     * @return Config_Storage_Mock
     */
    public static function inst() {
        if (NULL === self::$_inst) {
            self::$_inst = new MockStorage;
        }
        return self::$_inst;
    }

    private function  __construct() {
        // empty private constructor
    }


    public $storage = array();

    public function read($key) {
        $segments = explode('.', $key);
        $curr_itm = $this->storage;
        while(count($segments) > 0) {
            $curr_key = array_shift($segments);
            if ( ! isset($curr_itm[$curr_key]))
                return \cyclone\Config::NOT_FOUND;
            $curr_itm = &$curr_itm[$curr_key];
        }
        return $curr_itm;
    }

    public function write($key, $val) {
        $segments = explode('.', $key);
        $curr_itm = &$this->storage;
        while(TRUE) {
            $curr_key = array_shift($segments);
            if ( ! isset($curr_itm[$curr_key])) {
                if (count($segments) > 0) {
                    $curr_itm[$curr_key] = array();
                } else {
                    $curr_itm[$curr_key] = $val;
                    break;
                }
            }
            $curr_itm = &$curr_itm[$curr_key];
        }
    }

    public function clear() {
        $this->storage = array();
    }

}