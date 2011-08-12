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

    

    public function compile_expr(DB_Compiler $adapter) {
        $left = DB_Expression_Helper::compile_operand($this->left_operand, $adapter);
        $right = DB_Expression_Helper::compile_operand($this->right_operand, $adapter);
        return $left.' '.$this->operator.' '.$right;
    }

    public function  contains_table_name($table_name) {
        $tbl_name_len = strlen($table_name);

        if (is_string($this->left_operand) 
                && substr($this->left_operand, 0, $tbl_name_len) == $table_name)
            return TRUE;

        if (is_string($this->right_operand)
                && substr($this->right_operand, 0, $tbl_name_len) == $table_name)
            return TRUE;

        if ($this->left_operand instanceof DB_Expression
                && $this->left_operand->contains_table_name($table_name))
           return TRUE;

        if ($this->right_operand instanceof DB_Expression
                && $this->right_operand->contains_table_name($table_name))
           return TRUE;

        return FALSE;
    }
}
