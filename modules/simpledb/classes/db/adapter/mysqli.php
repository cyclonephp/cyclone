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
    
}