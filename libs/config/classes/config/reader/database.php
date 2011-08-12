<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package Config
 */
class Config_Reader_Database implements Config_Reader {

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
        $this->_query = DB::select($val_col)->from($table);
    }

    public function read($key) {
        if ( ! is_null($this->_group)) {
            $segments = explode('.', $key);
            $group = array_shift($segments);
            if ($group != $this->_group)
                return Config::NOT_FOUND;
            $key = implode('.', $segments);
        }
        $this->_query->where_conditions = array(new DB_Expression_Binary($this->_key_col, '=', DB::esc($key)));
        $result = $this->_query->exec($this->_database)->as_array();
        if (count($result) == 0)
            return Config::NOT_FOUND;
        return $result[0][$this->_val_col];
    }
}
