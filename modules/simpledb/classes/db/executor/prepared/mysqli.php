<?php

/**
 * Implementation of DB_Executor_Prepared for MySQLi
 */
class DB_Executor_Prepared_Mysqli extends DB_Executor_Prepared_Abstract {

    /**
     * Creates an prepared statement from the given SQL query
     *
     * @param string $sql
     * @return MySQLi_Stmt
     * @throws DB_Exception if the preparation fails.
     */
    public function prepare($sql) {
        $rval = $this->_db_conn->prepare($sql);
        if (FALSE === $rval) 
            throw new DB_Exception("'failed to prepare statement: '$sql' Cause: {$this->_db_conn->error}", $this->_db_conn->errno);

        return $rval;
    }

    private function add_type_string(array &$params) {
        $type_str = '';
        foreach ($params as $k => $param) {
            switch (gettype($param)) {
                case 'integer' :
                    $type_str .= 'i';
                    break;
                case 'float' :
                    $type_str .= 'd';
                    break;
                case 'string' :
                    $type_str .= 's';
                    break;
                case 'boolean' :
                    $type_str .= 'i';
                    // converting booleans to 0 / 1
                    $param = $param ? '1' : '0';
                    break;
                default:
                    if (is_array($param))
                        throw new DB_Exception('prepared statement parameters cannot be arrays');
                    // converting objects to their string representation
                    if (is_object($param)) {
                        $type_str .= 's';
                        $param = (string) $param;
                    }
            }
        }
        array_unshift($params, $type_str);
    }

    /**
     *
     * @param MySQLi_Stmt $prepared_stmt
     * @param array $params
     */
    public function exec_select($prepared_stmt, array $params
            , DB_Query_Select $orig_query) {
        if ( ! empty($params)) {
            $this->add_type_string($params);
            // I know that this foreach seems to be totally useless..
            // but please don't try to remove it, you will find yourself in a
            // fuckin' big shit of PHP
            $tmp = array();
            foreach ($params as $k => $p) $tmp [$k]= &$params[$k];
            call_user_func_array(array($prepared_stmt, 'bind_param'), $tmp);
        }
        $prepared_stmt->execute();
        $prepared_stmt->store_result();
        return new DB_Query_Result_Prepared_MySQLi($prepared_stmt, $orig_query);
    }

    public function exec_insert($prepared_stmt, array $params) {
        if ( ! empty($params)) {
            $this->add_type_string($params);
            $tmp = array();
            foreach ($params as $k => $p) $tmp [$k]= &$params[$k];
            call_user_func_array(array($prepared_stmt, 'bind_params'), $params);
        }
        $prepared_stmt->execute();
        return $this->_db_conn->insert_id;
    }

    public function exec_update($prepared_stmt, array $params) {
        if ( ! empty($params)) {
            $this->add_type_string($params);
            $tmp = array();
            foreach ($params as $k => $p) $tmp [$k]= &$params[$k];
            call_user_func_array(array($prepared_stmt, 'bind_params'), $params);
        }
        $prepared_stmt->execute();
        return $this->_db_conn->affected_rows;
    }

    public function exec_delete($prepared_stmt, array $params) {
        if ( ! empty($params)) {
            $this->add_type_string($params);
            $tmp = array();
            foreach ($params as $k => $p) $tmp [$k]= &$params[$k];
            call_user_func_array(array($prepared_stmt, 'bind_params'), $params);
        }
        $prepared_stmt->execute();
        return $this->_db_conn->affected_rows;
    }
    
}