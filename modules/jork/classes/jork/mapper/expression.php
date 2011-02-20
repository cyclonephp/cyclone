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

    private $_col_name;

    public function  __construct($resolved_db_expr) {
        $this->_db_expr = $resolved_db_expr;
    }

    public function  map_row(&$row) {
        if (NULL == $this->_col_name) {
            if (array_key_exists($this->_db_expr, $row)) {
                $this->_col_name = &$this->_db_expr;
            } else {
                $candidate = substr($this->_db_expr, strrpos($this->_db_expr, ' ')+1, strlen($this->_db_expr));
                if (array_key_exists($candidate, $row)) {
                    $this->_col_name = $candidate;
                } else
                    throw new JORK_Exception('failed to detect column name for database expression "'.$this->_db_expr.'"');
            }
        }
        return $row[$this->_col_name];
    }
}
