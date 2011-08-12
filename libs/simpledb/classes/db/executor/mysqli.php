<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
class DB_Executor_Mysqli extends DB_Executor_Abstract {

    public function  exec_select($sql) {
        $result = $this->_db_conn->query($sql);
        if ($result === false)
            throw new DB_Exception($this->_db_conn->error . ' ( ' . $sql . ' )', $this->_db_conn->errno);
        return new DB_Query_Result_Mysqli($result);
    }

    public function  exec_insert($sql, $return_insert_id) {
        if ( ! $this->_db_conn->query($sql))
            throw new DB_Exception($this->_db_conn->error, $this->_db_conn->errno);

        if ($return_insert_id)
            return $this->_db_conn->insert_id;

        return NULL;
    }

    public function  exec_update($sql) {
        if ( ! $this->_db_conn->query($sql))
            throw new DB_Exception($this->_db_conn->error, $this->_db_conn->errno);
        return $this->_db_conn->affected_rows;
    }

    public function  exec_delete($sql) {
        if ( ! $this->_db_conn->query($sql))
            throw new DB_Exception ($this->_db_conn->error, $this->_db_conn->errno);
        return $this->_db_conn->affected_rows;
    }

    public function exec_custom($sql) {
        $result = $this->_db_conn->multi_query($sql);
        if ( ! $result)
            throw new DB_Exception ('failed to execute query: '.$this->_db_conn->error
                    , $this->_db_conn->errno);
        $rval = array();
        do {
            $rval []= $this->_db_conn->store_result();
        } while ($this->_db_conn->more_results() && $this->_db_conn->next_result());
        return $rval;
    }
    
}
