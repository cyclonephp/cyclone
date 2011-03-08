<?php

class DB_Query_Result_Prepared_MySQLi extends DB_Query_Result {

    private $_stmt;

    public function  __construct(MySQLI_Stmt $stmt, DB_Query_Select $orig_query) {
        $this->_stmt = $stmt;
    }

    
    
}