<?php

/**
 * This class is reponsible for mapping custom database expressions of
 * the JORK query.
 * 
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package JORK
 */
class JORK_Mapper_Expression implements JORK_Mapper_Row {

    private $_db_expr;

    public $col_name;

    private $_last_value;

    public function  __construct($resolved_db_expr) {
        $this->_db_expr = $resolved_db_expr;
        $this->col_name = substr($this->_db_expr, strrpos($this->_db_expr, ' ')+1
                , strlen($this->_db_expr));
    }

    public function  map_row(&$row) {
        if ( ! array_key_exists($this->col_name, $row))
                throw new JORK_Exception('failed to detect column name for database expression "'
                        .$this->_db_expr.'"');
        
        return $this->_last_value = $row[$this->col_name];
    }

    public function  get_last_entity() {
        return array($this->_last_value, FALSE);
    }
}
