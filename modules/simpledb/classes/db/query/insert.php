<?php


class DB_Query_Insert {

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
}