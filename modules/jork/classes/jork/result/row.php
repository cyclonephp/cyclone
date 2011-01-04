<?php

class JORK_Result_Row extends ArrayObject {

    private $_mappers;

    public function  __construct($mappers, $db_row) {
        $this->_mappers = $mappers;
    }

    public function  offsetGet($index) {
        
    }
    
}