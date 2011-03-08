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
        $this->markTestSkipped('result handling is not yet implemented');
        //$this->assertEquals(2, count($result));
    }

    public function testPreparedResult() {
        $stmt = DB::connector()->db_conn->prepare('select id, name form cy_user');
        $stmt->execute();
        $result = new DB_Query_Result_Prepared_MySQLi($stmt, DB::select('id', 'name')->from('user'));
    }
}