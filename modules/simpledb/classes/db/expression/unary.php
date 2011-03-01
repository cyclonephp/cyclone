<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
class DB_Expression_Unary implements DB_Expression {

    public $operator;

    public $operand;

    public function  __construct($operator, $operand) {
        $this->operator = $operator;
        $this->operand = $operand;
    }

    public function  compile_expr(DB_Adapter $adapter) {
        $op = $this->operand instanceof DB_Expression ?
                $this->operand->compile() : $this->operand;

        return $this->operator.' '.$this->operand;
    }
}
