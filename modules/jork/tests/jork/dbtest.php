<?php


abstract class JORK_DbTest extends Kohana_Unittest_TestCase {

    public function  setUp() {
        $sql = file_get_contents(MODPATH.'jork/tests/testdata.sql');
        try {
            DB::inst('jork_test')->connect();
            DB::inst('jork_test')->exec_custom($sql);
            DB::inst('jork_test')->commit();
        } catch (DB_Exception $ex) {
            $this->markTestSkipped('failed to establish database connection jork_test');
        }
    }

    public function  tearDown() {
        JORK_InstancePool::clear();
    }

}