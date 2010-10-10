<?php


class DB_Adapter_Mysqli extends DB_Adapter {

    protected $mysqli;

    protected function connect() {
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

    public function  compile_select(DB_Query_Select $query) {
        $rval = 'SELECT ';
        $rval .= $this->escape_values($query->columns);
        $rval .= ' FROM '.$this->escape_values($query->tables);
        foreach ($query->joins as $join) {
            $rval .= ' '.$join['type'].' JOIN '.$this->escape_value($join['table']);
            $rval .= ' ON '.$this->compile_expressions($join['conditions']);
        }
        if ( ! empty($query->where_conditions)) {
            $rval .= ' WHERE '.$this->compile_expressions($query->where_conditions);
        }
        if ( ! empty($query->group_by)) {
            $rval .= ' GROUP BY '.$this->escape_values($query->group_by);
        }
        if ( ! empty($query->having_conditions)) {
            $rval .= ' HAVING '.$this->compile_expressions($query->having_conditions);
        }
        if ( ! empty($query->order_by)) {
            $rval .= ' ORDER BY ';
            foreach ($query->order_by as $ord) {
                $rval .= $this->escape_value($ord['column']).' '.$ord['direction'];
            }
        }
        if ( ! is_null($query->limit)) {
            $rval .= ' LIMIT '.$this->escape_value($query->limit);
        }
        if ( ! is_null($query->offset)) {
            $rval .= ' OFFSET '.$this->escape_value($query->offset);
        }
        return $rval;
    }

    public function  compile_insert(DB_Query_Insert $query) {
        $rval = 'INSERT INTO ';
        $rval .= $this->escape_identifier($query->table);
        $rval .= ' ('.$this->escape_values(array_keys($query->values)).')';
        $rval .= ' VALUES ('.$this->escape_params($query->values).')';
        return $rval;
    }

    public function  compile_update(DB_Query_Update $query) {
        $rval = 'UPDATE ';
        $rval .= $this->escape_identifier($query->table);
        $rval .= ' SET ';
        foreach ($query->values as $k => $v) {
            $pieces []= $this->escape_identifier($k).' = '.$this->escape_param($v);
        }
        $rval .= implode(', ', $pieces);
        if ( ! empty($query->conditions)) {
            $rval .= ' WHERE '.$this->compile_expressions($query->conditions);
        }
        return $rval;
    }

    public function  compile_delete(DB_Query_Delete $query) {
        $rval = 'DELETE FROM ';
        $rval .= $this->escape_identifier($query->table);
        if ( ! empty($query->conditions)) {
            $rval .= ' WHERE '.$this->compile_expressions($query->conditions);
        }
        if ( ! is_null($query->limit)) {
            $rval .= ' LIMIT '.$query->limit;
        }
        return $rval;
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

    public function escape_identifier($identifier) {
        $segments = explode('.', $identifier);
        return '`'.implode('`.`', $segments).'`';
    }

    public function escape_param($param) {
        return "'".$this->mysqli->real_escape_string($param)."'"; //TODO test & secure
    }

    public function compile_alias($expr, $alias) {
        return $expr.' AS `'.$alias.'`';
    }
    
}