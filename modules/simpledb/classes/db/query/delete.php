<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
class DB_Query_Delete implements DB_Query {

    public $table;

    public $conditions;

    public $limit;

    public function table($table) {
        $this->table = $table;
    }

    public function where() {
        $this->conditions []= DB::create_expr(func_get_args());
        return $this;
    }

    public function limit($limit) {
        $this->limit = (int) $limit;
        return $this;
    }

    public function compile($database = 'default') {
        return DB::compiler($database)->compile_delete($this);
    }

    public function exec($database = 'default') {
        $sql = DB::compiler($database)->compile_delete($this);
        return DB::executor($database)->exec_delete($sql);
    }

    public function  prepare($database = 'default') {
        $sql = DB::compiler($database)->compile_delete($this);
        return new DB_Query_Prepared_Delete($sql, $database);
    }
}
