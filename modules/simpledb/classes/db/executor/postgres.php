<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
class DB_Executor_Postgres extends DB_Executor_Abstract {

    private $_generator_sequences;

    public function  __construct($config, $db_conn) {
        parent::__construct($config, $db_conn);
        if (array_key_exists('pk_generator_sequences', $config)) {
            $this->_generator_sequences = $config['pk_generator_sequences'];
        } else {
            $this->_generator_sequences = array();
        }
    }

    public function exec_select($sql) {
        $result = pg_query($this->_db_conn, $sql);
        if (FALSE === $result)
            throw new DB_Exception("Failed to execute SQL: " . pg_last_error($this->_db_conn));

        return new DB_Query_Result_Postgres($result);
    }

    public function exec_insert($sql, $return_insert_id, $table = NULL) {
        if (pg_query($this->_db_conn, $sql) == FALSE)
            throw new DB_Exception('Failed to execute SQL: ' . pg_last_error($this->_db_conn));

        if ( ! $return_insert_id)
            return NULL;

        if (array_key_exists($table, $this->_generator_sequences)) {
            $seq_name = $this->_generator_sequences[$table];
        } else {
            $seq_name = $table . '_id_seq';
            $this->_generator_sequences[$table] = $seq_name;
        }
        $result = @pg_query($this->_db_conn, 'select currval(\'' . $seq_name
                . '\') as last_pk');

        if (FALSE === $result)
            throw new DB_Exception('Failed to retrieve the primary key of the inserted row');

        $row = pg_fetch_assoc($result);
        return $row['last_pk'];
    }

    public function exec_update($sql) {
        $result = pg_query($this->_db_conn, $sql);
        if (FALSE == $result)
            throw new DB_Exception('Failed to execute SQL: ' . pg_last_error($this->_db_conn));

        return pg_affected_rows($result);
    }

    public function exec_delete($sql) {
        $result = pg_query($this->_db_conn, $sql);
        if (FALSE == $result)
            throw new DB_Exception('Failed to execute SQL: ' . pg_last_error($this->_db_conn));

        return pg_affected_rows($result);
    }

    public function exec_custom($sql) {
        return pg_query($this->_db_conn, $sql);
    }
    
}
