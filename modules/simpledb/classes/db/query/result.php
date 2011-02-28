<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
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


    /**
     * Returns all the result rows as associative arrays.
     *
     * @return array
     */
    public abstract function as_array();

}
