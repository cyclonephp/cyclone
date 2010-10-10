<?php


class DB_Adapter_Mysqli extends DB_Adapter {

    protected $mysqli;

    public function connect() {
        $conn = $this->config['connection'];
        $this->mysqli = new mysqli($conn['host'], $conn['username'],
                $conn['password'], $conn['database']
                , Arr::get($conn, 'port',  ini_get('mysqli.default_port'))
                , Arr::get($conn, 'socket', ini_get('mysqli.default_socket')));
    }

    public function disconnect() {
        $this->mysqli->close();
    }

    public function  compile_select(DB_Query_Select $query) {
        ;
    }

    public function  compile_insert(DB_Query_Insert $query) {
        ;
    }

    public function  compile_update(DB_Query_Update $query) {
        ;
    }

    public function  compile_delete(DB_Query_Delete $query) {
        ;
    }

    public function  exec_select(DB_Query_Select $query) {
        $sql = $this->compile_select($query);
    }

    public function  exec_insert(DB_Query_Insert $query) {
        $sql = $this->compile_insert($query);
    }

    public function  exec_update(DB_Query_Update $query) {
        $sql = $this->compile_update($query);
    }

    public function  exec_delete(DB_Query_Delete $query) {
        $sql = $this->compile_delete($query);
    }

    public function  autocommit($autocommit) {
        $this->mysqli->autocommit($autocommit);
    }

    public function  commit() {
        $this->mysqli->commit();
    }

    public function rollback() {
        $this->mysqli->rollback();
    }
    
}