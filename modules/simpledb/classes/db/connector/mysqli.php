<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
class DB_Connector_Mysqli extends DB_Connector_Abstract {

    public function connect() {
        $conn = $this->_config['connection'];
        $this->db_conn = @new mysqli($conn['host'], $conn['username'],
                $conn['password'], $conn['database']
                , Arr::get($conn, 'port',  ini_get('mysqli.default_port'))
                , Arr::get($conn, 'socket', ini_get('mysqli.default_socket')));
        if (mysqli_connect_errno())
            throw new DB_Exception('failed to connect: '.mysqli_connect_error());
    }

    public function disconnect() {
        // safely disconnecting to avoid errors caused by double disconnects
        @$this->db_conn->close();
    }

    public function  autocommit($autocommit) {
         if ( ! $this->db_conn->autocommit($autocommit))
            throw new DB_Exception ('failed to change autocommit mode');
    }

    public function  commit() {
        if ( ! $this->db_conn->commit())
            throw new DB_Exception('failed to commit transaction: '
                    .$this->db_conn->error);
    }

    public function rollback() {
        if ( ! $this->db_conn->rollback())
            throw new DB_Exception('failed to rollback transaction');
    }
    
}
