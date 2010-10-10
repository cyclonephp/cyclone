<?php

abstract class DB_Adapter {

    protected $config;

    public function  __construct($config) {
        $this->config = $config;
        $this->connect();
    }

    public abstract function connect();

    public abstract function disconnect();

    public function compile($query) {
        switch (get_class($query)) {
            case 'DB_Query_Select':
                return $this->compile_select($query);
            case 'DB_Query_Insert':
                return $this->compile_insert($query);
            case 'DB_Query_Update':
                return $this->compile_update($query);
            case 'DB_Query_Delete':
                return $this->compile_delete($query);
            default:
                throw new SimpleDB_Exception('unknown query type');
        }
    }

    public function exec($query) {
        switch (get_class($query)) {
            case 'DB_Query_Select':
                return $this->exec_select($query);
            case 'DB_Query_Insert':
                return $this->exec_insert($query);
            case 'DB_Query_Update':
                return $this->exec_update($query);
            case 'DB_Query_Delete':
                return $this->exec_delete($query);
            default:
                throw new SimpleDB_Exception('unknown query type');
        }
    }

    /**
     * @param boolean $autocommit
     */
    abstract function autocommit($autocommit);

    abstract function compile_select(DB_Query_Select $query);

    abstract function compile_insert(DB_Query_Insert $query);

    abstract function compile_update(DB_Query_Update $query);

    abstract function compile_delete(DB_Query_Delete $query);

    abstract function exec_select($query);

    abstract function exec_insert($query);

    abstract function exec_update($query);

    abstract function exec_delete($query);
    
}