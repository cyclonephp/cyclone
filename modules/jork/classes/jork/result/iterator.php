<?php


class JORK_Result_Iterator implements Iterator {

    /**
     * @var DB_Query_Result
     */
    private $_result;

    /**
     * @var array<JORK_Mapper_Result>
     */
    private $_mappers;

    /**
     * @var int the index of the current element
     */
    private $_idx;

    /**
     * @var int the total count of items in the result
     */
    private $_count;

    public function  __construct($object_result) {
        $this->_result = $object_result;
        $this->_count = count($object_result);
    }
    
    public function  rewind() {
        $this->_idx = 0;
    }

    public function  next() {
        ++$this->_idx;
    }

    public function  valid() {
        return $this->_idx < $this->_count;
    }

    public function  current() {
        return $this->_result[$this->_idx];
    }

    public function  key() {
        return $this->_idx;
    }
    
}