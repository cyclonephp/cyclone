<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
class DB_Query_Custom implements DB_Query {

    protected $sql;

    public function  __construct($sql) {
        $this->sql = $sql;
    }

    public function compile($database = 'default') {
        return $this->sql;
    }

    public function exec($database = 'default') {
        return DB::executor($database)->exec_custom($this->sql);
    }

    public function  prepare($database = 'default') {
        return new DB_Query_Prepared_Custom($this->sql, $database);
    }
}
