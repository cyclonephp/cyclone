<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
class DB_Expression_Binary implements DB_Expression {

    public $operator;

    public $left_operand;

    public $right_operand;

    public function  __construct($left_operand, $operator, $right_operand) {
        $this->left_operand = $left_operand;
        $this->operator = $operator;
        $this->right_operand = $right_operand;
    }

    

    public function compile_expr(DB_Adapter $adapter) {
        $left = DB_Expression_Helper::compile_operand($this->left_operand, $adapter);
        $right = DB_Expression_Helper::compile_operand($this->right_operand, $adapter);
        return $left.' '.$this->operator.' '.$right;
    }
}
