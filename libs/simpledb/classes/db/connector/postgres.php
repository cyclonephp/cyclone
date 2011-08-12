<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
class DB_Connector_Postgres extends DB_Connector_Abstract {

    public function connect() {
        $conn_params = array();
        if (array_key_exists('persistent', $this->_config['connection'])) {
            $persistent = $this->_config['connection']['persistent'];
            unset($this->_config['connection']['persistent']);
        } else {
            $persistent = FALSE;
        }
        foreach ($this->_config['connection'] as $k => $v) {
            $conn_params []= "$k=$v";
        }
        $conn_str = implode(' ', $conn_params);
        if ($persistent) {
            $this->db_conn = @pg_pconnect($conn_str);
        } else {
            $this->db_conn = @pg_connect($conn_str);
        }
        if (FALSE == $this->db_conn)
            throw new DB_Exception('failed to connect to database: '.$conn_str);

        if (array_key_exists('pk_generator_sequences', $this->_config)) {
            $this->_generator_sequences = $this->_config['pk_generator_sequences'];
        } else {
            $this->_generator_sequences = array();
        }
    }

    public function  disconnect() {
        if ( ! pg_close($this->db_conn))
            throw new DB_Exception("failed to disconnect from database '{$this->_config['connection']['dbname']}'");
    }

    /**
     * @param boolean $autocommit
     */
    public function autocommit($autocommit) {

    }

    public function commit() {

    }

    public function rollback() {

    }
    
}
