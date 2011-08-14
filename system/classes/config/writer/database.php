<?php

class Config_Writer_Database implements Config_Writer {

    private $_table;

    private $_key_col;

    private $_val_col;

    private $_database;

    private $_group;

    private $_query;

    public function  __construct($table, $key_col, $val_col, $database = 'default', $group = NULL) {
        $this->_table = $table;
        $this->_key_col = $key_col;
        $this->_val_col = $val_col;
        $this->_database = $database;
        $this->_group = $group;
        $this->_query = DB::update($table);
    }

    public function  write($key, $val) {
        $this->_query->values = array(
            $this->_val_col => $val
        );
        $this->_query->conditions = array(new DB_Expression_Binary($this->_key_col, '=', DB::esc($key)));
        $count = $this->_query->exec($this->_database);
        return $count != 0;
    }
    
}