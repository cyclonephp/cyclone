<?php


class DB_Expression_Unary implements DB_Expression {

    public $operator;

    public $operand;

    public function  __construct($operator, $operand) {
        $this->operator = $operator;
        $this->operand = $operand;
    }
}