<?php

namespace cyclone\config;

interface Writer {

    /**
     * Returns TRUE if successfully updated the config $key, FALSE if the
     * config key is not found in the data source of the writer
     * 
     * @return boolean
     */
    public function write($key, $val);
    
}