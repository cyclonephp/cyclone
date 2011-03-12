<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
class DB_Query_Insert implements DB_Query {

    public $table;

    public $values;

    public function table($table) {
        $this->table = $table;
        return $this;
    }

    public function values($values) {
        $this->values []= $values;
        return $this;
    }

    public function compile($database = 'default') {
        return DB::compiler($database)->compile_insert($this);
    }

    public function exec($database = 'default', $return_insert_id = TRUE) {
        $sql = DB::compiler($database)->compile_insert($this);
        return DB::executor($database)->exec_insert($sql, $return_insert_id, $this->table);
    }

    public function  prepare($database = 'default') {
        $sql = DB::compiler($database)->compile_insert($this);
        return new DB_Query_Prepared_Insert($sql, $database);
    }
}
