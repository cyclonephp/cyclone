<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package Config
 */
class Config_Reader_Database implements Config_Reader {

    public function  read($key) {
        return $key;
    }
}
