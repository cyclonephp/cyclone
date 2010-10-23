<?php

class Config_Reader_File implements Config_Reader {

    public function read($key) {
        return $key;
    }
}