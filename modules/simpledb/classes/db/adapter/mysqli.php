<?php


class DB_Adapter_Mysqli extends DB_Adapter {

    /**
     *
     * @var mysqli
     */
    protected $mysqli;

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

    protected function _select_aliases($tables, $joins = NULL){
        if(is_array($tables)){
            foreach($tables as $table){
                if(is_array($table)){
                    $this->_table_aliases[] = $table[1];
                }
            }
        }
        if (is_array($joins)){
            foreach($joins as $join){
                if(is_array($join['table'])){
                    $this->_table_aliases[] = $join['table'][1];
                }
            }
        }
    }

    public function  compile_select(DB_Query_Select $query) {
        $this->_select_aliases($query->tables, $query->joins);
        $rval = 'SELECT ';
        $rval .= $this->escape_values($query->columns);
        $rval .= ' FROM ';
        $tbl_names = array();
        foreach ($query->tables as $table) {
            $tbl_names []= $this->escape_table($table);
        }
        $rval .= implode(', ', $tbl_names);
        foreach ($query->joins as $join) {
            $rval .= ' '.$join['type'].' JOIN '.$this->escape_table($join['table']);
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
            $rval .= ' LIMIT '.$query->limit;
        }
        if ( ! is_null($query->offset)) {
            $rval .= ' OFFSET '.$query->offset;
        }
        if ( ! empty($query->unions)) {
            foreach($query->unions as $union){
            $rval .= ' UNION ';
            if ($union['all'] == TRUE){
                $rval .= 'ALL ';
            }
            $rval .= $this->compile_select($union['select']);
            }
        }
        return $rval;
    }

    public function  compile_insert(DB_Query_Insert $query) {
        $this->_select_aliases($query->table);
        $rval = 'INSERT INTO ';
        $rval .= $this->escape_table($query->table);
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
        $this->_select_aliases($query->table);
        $rval = 'UPDATE ';
        $rval .= $this->escape_table($query->table);
        $rval .= ' SET ';
        foreach ($query->values as $k => $v) {
            $pieces []= $this->escape_identifier($k).' = '.$this->escape_param($v);
        }
        $rval .= implode(', ', $pieces);
        if ( ! empty($query->conditions)) {
            $rval .= ' WHERE '.$this->compile_expressions($query->conditions);
        }
        if ( ! is_null($query->limit)) {
            $rval .= ' LIMIT '.$query->limit;
        }
        return $rval;
    }

    public function  compile_delete(DB_Query_Delete $query) {
        $this->_select_aliases($query->table);
        $rval = 'DELETE FROM ';
        $rval .= $this->escape_table($query->table);
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
        return $this->mysqli->insert_id;
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

    public function escape_identifier($identifier) {
        if ($identifier instanceof DB_Expression)
            return $identifier->compile_expr($this);
        $segments = explode('.', $identifier);
        $rval = '`'.$segments[0].'`';
        if(array_key_exists('prefix', $this->config) && count($segments) == 2){
            if( ! in_array($segments[0], $this->_table_aliases)){
                $rval =  '`'.$this->config['prefix'].$segments[0].'`';
            }
        }
        
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