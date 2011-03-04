<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
abstract class DB_Connector_Abstract implements DB_Connector {

    public $db_conn;

    protected $_config;

    public function  __construct($config) {
        $this->_config = $config;
        $this->connect();
    }

}
