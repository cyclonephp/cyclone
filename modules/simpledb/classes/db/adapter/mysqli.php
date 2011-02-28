<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
class DB_Adapter_Mysqli extends DB_Adapter {

    /**
     *
     * @var mysqli
     */
    protected $mysqli;

    protected $esc_char = '`';

    public function connect() {
        $conn = $this->config['connection'];
        $this->mysqli = @new mysqli($conn['host'], $conn['username'],
                $conn['password'], $conn['database']
                , Arr::get($conn, 'port',  ini_get('mysqli.default_port'))
                , Arr::get($conn, 'socket', ini_get('mysqli.default_socket')));
        if (mysqli_connect_errno())
            throw new DB_Exception('failed to connect: '.mysqli_connect_error());
    }

    public function disconnect() {
        $this->mysqli->close();
    }

    public function  exec_select(DB_Query_Select $query) {
        $sql = $this->compile_select($query);
        $result = $this->mysqli->query($sql);
        if ($result === false)
            throw new DB_Exception($this->mysqli->error, $this->mysqli->errno);
        return new DB_Query_Result_Mysqli($result);
    }

    public function  exec_insert(DB_Query_Insert $query, $return_insert_id) {
        $sql = $this->compile_insert($query);
        if ( ! $this->mysqli->query($sql))
            throw new DB_Exception($this->mysqli->error, $this->mysqli->errno);

        if ($return_insert_id)
            return $this->mysqli->insert_id;
        
        return NULL;
    }

    public function  exec_update(DB_Query_Update $query) {
        $sql = $this->compile_update($query);
        if ( ! $this->mysqli->query($sql))
            throw new DB_Exception($this->mysqli->error, $this->mysqli->errno);
        return $this->mysqli->affected_rows;
    }

    public function  exec_delete(DB_Query_Delete $query) {
        $sql = $this->compile_delete($query);
        if ( ! $this->mysqli->query($sql))
            throw new DB_Exception ($this->mysqli->error, $this->mysqli->errno);
        return $this->mysqli->affected_rows;
    }

    public function exec_custom($sql) {
        $result = $this->mysqli->multi_query($sql);
        if ( ! $result)
            throw new DB_Exception ('failed to execute query: '.$this->mysqli->error
                    , $this->mysqli->errno);
        $rval = array();
        do {
            $rval []= $this->mysqli->store_result();
        } while ($this->mysqli->more_results() && $this->mysqli->next_result());
        return $rval;
    }

    public function compile_hints($hints) {
        $rval = " USE";
        foreach ($hints as $hint) {
            $rval .= ' ' . $hint;
        };
        return $rval;
    }

    public function  autocommit($autocommit) {
         if ( ! $this->mysqli->autocommit($autocommit))
            throw new DB_Exception ('failed to change autocommit mode');
    }

    public function  commit() {
        if ( ! $this->mysqli->commit())
            throw new DB_Exception('failed to commit transaction: '
                    .$this->mysqli->error);
    }

    public function rollback() {
        if ( ! $this->mysqli->rollback())
            throw new DB_Exception('failed to rollback transaction');
    }

    public function escape_table($table){
        if ($table instanceof DB_Expression)
            return $table->compile_expr($this);
        
        $prefix = array_key_exists('prefix', $this->config)
                ? $this->config['prefix']
                : '';

        if (is_array($table)) {
            $rtable = '`' . $prefix . $table[0] . '` `' . $table[1] . '`';
        } else {
            $rtable = '`' . $prefix . $table . '`';
        }

        return $rtable;
    }

    public function escape_param($param) {
        if (NULL === $param)
            return 'NULL';
        
        return "'".$this->mysqli->real_escape_string($param)."'"; //TODO test & secure
    }

    public function compile_alias($expr, $alias) {
        return $expr.' AS `'.$alias.'`';
    }
    
}
