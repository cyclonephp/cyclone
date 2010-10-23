<?php

class Config_Reader_Database implements Config_Reader {

    public function  read($key) {
        return $key;
    }
}