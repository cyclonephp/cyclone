<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
class DB_Expression_Custom implements DB_Expression {

    public $str;

    public function  __construct($str) {
        $this->str = $str;
    }

    public function  compile_expr(DB_Adapter $adapter) {
        return $this->str;
    }

}
