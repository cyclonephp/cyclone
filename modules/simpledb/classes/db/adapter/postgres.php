<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
class DB_Adapter_Postgres extends DB_Adapter {

    protected $_db_conn;

    protected $esc_char = '"';

    private $_generator_sequences;

    public function connect() {
        $conn_params = array();
        if (array_key_exists('persistent', $this->config['connection'])) {
            $persistent = $this->config['connection']['persistent'];
            unset($this->config['connection']['persistent']);
        } else {
            $persistent = FALSE;
        }
        foreach ($this->config['connection'] as $k => $v) {
            $conn_params []= "$k=$v";
        }
        $conn_str = implode(' ', $conn_params);
        if ($persistent) {
            $this->_db_conn = @pg_pconnect($conn_str);
        } else {
            $this->_db_conn = @pg_connect($conn_str);
        }
        if (FALSE == $this->_db_conn)
            throw new DB_Exception('failed to connect to database: '.$conn_str);

        if (array_key_exists('pk_generator_sequences', $this->config)) {
            $this->_generator_sequences = $this->config['pk_generator_sequences'];
        } else {
            $this->_generator_sequences = array();
        }
    }

    public function  disconnect() {
        if ( ! pg_close($this->_db_conn))
            throw new DB_Exception("failed to disconnect from database '{$this->config['connection']['dbname']}'");
    }

    public function  compile_hints($hints) {
        throw new DB_Exception("postgres doesn't support hints");
    }

    public function exec_select(DB_Query_Select $query) {
        $sql = $this->compile_select($query);
        $result = pg_query($this->_db_conn, $sql);
        if (FALSE === $result)
            throw new DB_Exception("Failed to execute SQL: " . pg_last_error($this->_db_conn));

        return new DB_Query_Result_Postgres($result);
    }

    public function exec_insert(DB_Query_Insert $query, $return_insert_id) {
        $sql = $this->compile_insert($query);
        if (pg_query($this->_db_conn, $sql) == FALSE)
            throw new DB_Exception('Failed to execute SQL: ' . pg_last_error($this->_db_conn));

        if ( ! $return_insert_id)
            return NULL;

        if (array_key_exists($query->table, $this->_generator_sequences)) {
            $seq_name = $this->_generator_sequences[$query->table];
        } else {
            $seq_name = $query->table . '_id_seq';
            $this->_generator_sequences[$query->table] = $seq_name;
        }
        $result = pg_query($this->_db_conn, 'select currval(\'' . $seq_name
                . '\') as last_pk');
        
        if (FALSE === $result)
            throw new DB_Exception('Failed to retrieve the primary key of the inserted row');

        $row = pg_fetch_assoc($result);
        return $row['last_pk'];
    }

    public function exec_update(DB_Query_Update $query) {
        $sql = $this->compile_update($query);
        if (pg_query($this->_db_conn, $sql) == FALSE)
            throw new DB_Exception('Failed to execute SQL: ' . pg_last_error($this->_db_conn));
    }

    public function exec_delete(DB_Query_Delete $query) {
        $sql = $this->compile_delete($query);
        if (pg_query($this->_db_conn, $sql) == FALSE)
            throw new DB_Exception('Failed to execute SQL: ' . pg_last_error($this->_db_conn));
    }

    public function exec_custom($sql) {
        return pg_query($this->_db_conn, $sql);
    }

    public function compile_alias($expr, $alias) {
        return $expr.' AS "'.$alias.'"';
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

    /**
     * This method is responsible for prventing SQL injection.
     *
     * @param string $param user parameter that should be escaped
     */
    public function escape_param($param) {
        if (NULL === $param)
            return 'NULL';

        return "'" . pg_escape_string($this->_db_conn, $param) . "'";
    }

    public function escape_table($table) {
        if ($table instanceof DB_Expression)
            return $table->compile_expr($this);

        $prefix = array_key_exists('prefix', $this->config)
                ? $this->config['prefix']
                : '';

        if (is_array($table)) {
            $rtable = '"' . $prefix . $table[0] . '" "' . $table[1] . '"';
        } else {
            $rtable = '"' . $prefix . $table . '"';
        }

        return $rtable;
    }


    
}
