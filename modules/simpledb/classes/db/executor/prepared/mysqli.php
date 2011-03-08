<?php

class DB_Executor_Prepared_Mysqli extends DB_Executor_Prepared_Abstract {

    public function prepare($sql) {
        $rval = $this->_db_conn->prepare($sql);
        if (FALSE === $rval) 
            throw new DB_Exception("'failed to prepare statement: '$sql' Cause: {$this->_db_conn->error}", $this->_db_conn->errno);

        return $rval;
    }

    public function exec_select($prepared_stmt, array $params) {
        
    }

    public function exec_insert($prepared_stmt, array $params) {
        
    }

    public function exec_update($prepared_stmt, array $params) {
        
    }

    public function exec_delete($prepared_stmt, array $params) {
        
    }
    
}