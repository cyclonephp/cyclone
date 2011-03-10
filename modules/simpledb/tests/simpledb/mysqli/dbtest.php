<?php


abstract class SimpleDB_Mysqli_DbTest extends Kohana_Unittest_TestCase {

    public function setUp() {
        DB::clear_connections();
        try {
            DB::query('truncate cy_user')->exec();
            $names = array('user1', 'user2');
            $insert = DB::insert('user');
            foreach ($names as $name) {
                $insert->values(array('name' => $name));
            }
            $insert->exec();
        } catch (Exception $ex) {
            error_log($ex->getMessage() . ' class: ' . get_class($this));
            $this->markTestSkipped('skipping simpledb tests');
        }
    }

    public function tearDown() {
        DB::clear_connections();
    }
}