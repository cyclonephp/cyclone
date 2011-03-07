<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
class DB_Expression_Param implements DB_Expression {
    
    protected $val;

    public function  __construct($val) {
        $this->val = $val;
    }

    public function compile_expr(DB_Compiler $adapter) {
        return $adapter->escape_param($this->val);
    }

    public function  contains_table_name($table_name) {
        return FALSE;
    }

}
