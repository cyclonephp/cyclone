<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
interface DB_Connector {

    public function connect();

    public function disconnect();

}
