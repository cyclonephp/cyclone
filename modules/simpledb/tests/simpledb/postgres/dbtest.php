<?php


abstract class SimpleDB_Postgres_DbTest extends Kohana_Unittest_TestCase {

    public function setUp() {
        DB::query('delete from user')->exec('postgres');
    }

    
    
}