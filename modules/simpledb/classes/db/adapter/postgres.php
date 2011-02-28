<?php


class DB_Adapter_Postgres extends DB_Adapter {

    protected $_db_conn;

    protected $esc_char = '"';

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
            $this->_db_conn = pg_pconnect($conn_str);
        } else {
            $this->_db_conn = pg_connect($conn_str);
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
        
    }

    public function exec_insert(DB_Query_Insert $query) {
        
    }

    public function exec_update(DB_Query_Update $query) {
        
    }

    public function exec_delete(DB_Query_Delete $query) {
        
    }

    public function exec_custom($sql) {
        
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