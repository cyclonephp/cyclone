<?php


class SimpleDB_Mysqli_PreparedTest extends SimpleDB_Mysqli_DbTest {


    public function testPrepare() {
        $stmt = DB::executor_prepared()->prepare('select * from cy_user');
        $this->assertInstanceOf('MySQLi_Stmt', $stmt);
    }

    /**
     * @expectedException DB_Exception
     * @expectedExceptionMessage failed to prepare statement: 'select * from dummy' Cause: Table 'simpledb.dummy' doesn't exist
     */
    public function testPrepareFailure() {
        $stmt = DB::executor_prepared()->prepare('select * from dummy');
    }


    public function testExecSelect() {
        $result = DB::select()->from('user')->prepare()->exec();
        //$this->assertEquals(2, count($result));
    }
}