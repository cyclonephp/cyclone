<?php

namespace cyclone\config\writer;
use cyclone\db;
use cyclone as cy;

class Database implements \cyclone\config\Writer {

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
        $this->_query = cy\DB::update($table);
    }

    public function  write($key, $val) {
        $this->_query->values = array(
            $this->_val_col => $val
        );
        $this->_query->conditions = array(new db\BinaryExpression($this->_key_col
                , '=', db\DB::esc($key)));
        $count = $this->_query->exec($this->_database);
        return $count != 0;
    }
    
}