<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
class DB_Query_Prepared_Select extends DB_Query_Prepared_Abstract {

    private $_query;

    public function  __construct($sql, $database, DB_Query_Select $query) {
        parent::__construct($sql, $database);
        $this->_query = $query;
    }

    public function exec() {
        return $this->_executor->exec_select($this->_prepared_stmt
                , $this->_params, $this->_query);
    }
    
}
