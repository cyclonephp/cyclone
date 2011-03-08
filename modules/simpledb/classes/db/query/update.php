<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
class DB_Query_Update implements DB_Query {

    public $table;

    public $values;

    public $conditions;

    public $limit;

    public function table($table) {
        $this->table = $table;
        return $this;
    }

    public function values($values) {
        $this->values = $values;
        return $this;
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
        return DB::compiler($database)->compile_update($this);
    }

    public function exec($database = 'default') {
        $sql = DB::compiler($database)->compile_update($this);
        return DB::executor($database)->exec_update($sql);
    }

    public function  prepare($database = 'default') {
        $sql = DB::compiler($database)->compile_update($this);
        return new DB_Query_Prepared_Update($sql, $database);
    }

}
