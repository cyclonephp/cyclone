<?php

abstract class DB_Query_Prepared_Abstract implements DB_Query_Prepared {

    protected $_params = array();

    /**
     * @param string $key
     * @param scalar $value
     * @return DB_Query_Prepared_Abstract
     */
    public function param($key, $value) {
        $this->_params[$key] = $value;
        return $this;
    }

    /**
     * @param array $params
     * @return DB_Query_Prepared_Abstract
     */
    public function params(array $params) {
        $this->_params = $params;
        return $this;
    }

    /**
     * @return DB_Query_Result
     */
    abstract function exec();

    
}