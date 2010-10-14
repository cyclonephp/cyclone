<?php


class DB_Adapter_Mysqli extends DB_Adapter {

    /**
     *
     * @var mysqli
     */
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
        if (empty($query->values))
            throw new DB_Exception('no value lists to be inserted');
        $rval .= ' ('.$this->escape_values(array_keys($query->values[0])).') VALUES ';
        foreach ($query->values as $value_set) {
            $value_sets []= '('.$this->escape_params($value_set).')';
        }
        
        $rval .= implode(', ', $value_sets);
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
        $result = $this->mysqli->query($sql);
        if ($result === false)
            throw new DB_Exception($this->mysqli->error, $this->mysqli->errno);
        return new DB_Query_Result_Mysqli($result);
    }

    public function  exec_insert(DB_Query_Insert $query) {
        $sql = $this->compile_insert($query);
        if ( ! $this->mysqli->query($sql))
            throw new DB_Exception($this->mysqli->error, $this->mysqli->errno);
        return $this->mysqli->affected_rows;
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

    public function  autocommit($autocommit) {
         if ( ! $this->mysqli->autocommit($autocommit))
            throw new DB_Exception ('failed to change autocommit mode');
    }

    public function  commit() {
        if ( ! $this->mysqli->commit())
            throw new DB_Exception('failed to commit transaction');
    }

    public function rollback() {
        if ( ! $this->mysqli->rollback())
            throw new DB_Exception('failed to rollback transaction');
    }

    public function escape_identifier($identifier) {
        if ($identifier instanceof DB_Expression)
            return $identifier->compile_expr($this);
        $segments = explode('.', $identifier);
        $rval = '`'.$segments[0].'`';
        if (count($segments) > 1) {
            if ('*' == $segments[1]) {
                $rval .= '.*';
            } else {
                $rval .= '.`'.$segments[1].'`';
            }
        }
        return $rval;
    }

    public function escape_param($param) {
        return "'".$this->mysqli->real_escape_string($param)."'"; //TODO test & secure
    }

    public function compile_alias($expr, $alias) {
        return $expr.' AS `'.$alias.'`';
    }
    
}