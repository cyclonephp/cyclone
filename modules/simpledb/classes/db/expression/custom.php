<?php


class DB_Expression_Custom implements DB_Expression {

    public $str;

    public function  __construct($str) {
        $this->str = $str;
    }

}