<?php

class DB_Executor_Prepared_Mysqli extends DB_Executor_Prepared_Abstract {

    public function prepare($sql) {
        $rval = $this->_db_conn->prepare($sql);
        if (FALSE === $rval) 
            throw new DB_Exception("'failed to prepare statement: '$sql' Cause: {$this->_db_conn->error}", $this->_db_conn->errno);

        return $rval;
    }

    private function get_type_string(array &$params) {
        $rval = '';
        foreach ($params as &$param) {
            switch (gettype($param)) {
                case 'integer' :
                    $rval .= 'i';
                    break;
                case 'float' :
                    $rval .= 'd';
                    break;
                case 'string' :
                    $rval .= 's';
                    break;
                case 'boolean' :
                    $rval .= 'i';
                    // converting booleans to 0 / 1
                    $param = $param ? '1' : '0';
                    break;
                default:
                    if (is_array($param))
                        throw new DB_Exception('prepared statement parameters cannot be arrays');
                    // converting objects to their string representation
                    if (is_object($param)) {
                        $rval .= 's';
                        $param = $param->__toString();
                    }
            }
        }
        return $rval;
    }

    /**
     *
     * @param MySQLi_Stmt $prepared_stmt
     * @param array $params
     */
    public function exec_select($prepared_stmt, array $params
            , DB_Query_Select $orig_query) {
        if ( ! empty($params)) {
            $type_str = $this->get_type_string($params);
            array_unshift($params, $type_str);
            call_user_func_array(array($prepared_stmt, 'bind_params'), $params);
        }
        $prepared_stmt->execute();
    }

    public function exec_insert($prepared_stmt, array $params) {
        
    }

    public function exec_update($prepared_stmt, array $params) {
        
    }

    public function exec_delete($prepared_stmt, array $params) {
        
    }
    
}