<?php


class DB_Query_Result_Postgres extends DB_Query_Result {

    private $_res;

    public function  __construct($res) {
        $this->_res = $res;
    }
    
}