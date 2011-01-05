<?php


class JORK_Result_Iterator implements Iterator {

    /**
     *
     * @var DB_Query_Result
     */
    protected $_db_result;

    /**
     *
     * @var array<JORK_Mapper_Result>
     */
    protected $_mappers;

    public function  __construct($db_result, $mappers) {
        $this->_db_result = $db_result;
        $this->_mappers = $mappers;
    }
    
    public function  rewind() {
        $this->_db_result->rewind();
    }

    public function  next() {
        ;
    }

    public function  valid() {
        ;
    }

    public function  current() {

    }

    public function  key() {
        ;
    }
    
}