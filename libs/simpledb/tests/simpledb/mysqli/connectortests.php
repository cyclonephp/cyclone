<?php


class SimpleDB_MySQLi_ConnectorTest extends Kohana_Unittest_TestCase {

    public function testPersistentConnection() {

       $test = new DB_Connector_MySQLi(array(
    'adapter' => 'mysqli',
    'prefix' => 'cy_',
    'connection' => array(
        'username' => 'simpledb',
        'password' => 'simpledb',
        'database' => 'simpledb',
        'host' => 'localhost',
        'presistent' => TRUE
    ))
);


    }

}

?>
