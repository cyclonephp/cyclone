<?php


abstract class DB_Query_Result extends ArrayIterator implements Countable, Traversable {

    protected $_row_type = 'array';

    protected $_index_by;

    private $_current_row;

    private $_idx = -1;

    public function rows($type) {
        $this->_row_type = $type;
        return $this;
    }

    public function index_by($column) {
        $this->_index_by = $column;
        return $this;
    }


    public abstract function as_array();

}