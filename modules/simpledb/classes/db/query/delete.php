<?php


class DB_Query_Delete {

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
        $this->limit = $limit;
        return $this;
    }
}