<?php


class DB_Query_Insert extends DB_Query {

    public $table;

    public $values;

    public function table($table) {
        $this->table = $table;
        return $this;
    }

    public function values($values) {
        $this->values = $values;
        return $this;
    }

    public function compile($database = 'default') {
        return DB::inst($database)->compile_insert($this);
    }

    public function exec($database = 'default') {
        return DB::inst($database)->exec_insert($this);
    }
}