<?php


class DB_Expression_Param implements DB_Expression {
    
    protected $val;

    public function  __construct($val) {
        $this->val = $val;
    }

    public function compile_expr(DB_Adapter $adapter) {
        return $adapter->escape_param($this->val);
    }

}