<?php

class JORK_Alias_Factory {

    protected $aliases = array();

    public function for_table($table_name) {
        if ( !array_key_exists($table_name, $this->aliases)) {
            $this->aliases[$table_name] = 0;
        }
        return $table_name.'_'.(++$this->aliases[$table_name]);
    }
}