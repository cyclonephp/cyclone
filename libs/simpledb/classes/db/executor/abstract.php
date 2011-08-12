<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
abstract class DB_Executor_Abstract implements DB_Executor {

    protected $_config;


    protected $_db_conn;

    public function  __construct($config, $db_conn) {
        $this->_config = $config;
        $this->_db_conn = $db_conn;
    }
    
}
