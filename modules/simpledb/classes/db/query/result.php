<?php


abstract class DB_Query_Result extends ArrayIterator implements Countable, Traversable {

    protected $row_type = 'array';

    protected $index_by;

    public function rows($type) {
        $this->row_type = $type;
        return $this;
    }

    public function index_by($column) {
        $this->index_by = $column;
    }

}