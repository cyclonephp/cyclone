<?php


class DB_Query_Update {

    public $table;

    public $values;

    public $conditions;

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
    }

    public function compile($database = 'default') {
        return DB::inst($database)->compile_update($this);
    }

    public function exec($database = 'default') {
        return DB::inst($database)->exec_update($this);
    }

}