<?php


class JORK_Mapper_Expression implements JORK_Mapper_Result {

    private $_db_expr;

    public function  __construct($resolved_db_expr) {
        $this->_db_expr = $resolved_db_expr;
    }
}