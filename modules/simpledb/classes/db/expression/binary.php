<?php


class DB_Expression_Binary implements DB_Expression {

    public $operator;

    public $left_operand;

    public $right_operand;

    public function  __construct($left_operand, $operator, $right_operand) {
        $this->left_operand = $left_operand;
        $this->operator = $operator;
        $this->right_operand = $right_operand;
    }
}