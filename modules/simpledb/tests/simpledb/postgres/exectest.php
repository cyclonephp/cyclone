<?php

class SimpleDB_Query_Result_ExecTest extends SimpleDB_Postgres_DbTest {

    public function testExecInsert() {
        $id = DB::insert('users')->values(array('name' => 'user3'))->exec('postgres');
        //$count = count(DB::select()->from('users')->exec('postgres')->as_array());
        //$this->assertEquals(3, $count);
        $this->assertEquals(3, $id);

        $id = DB::insert('serusers')->values(array('name' => 'user1'))->exec('postgres');
        $this->assertEquals(3, $id);

        $id = DB::insert('users')->values(array('name' => 'user1'))->exec('postgres', FALSE);
        $this->assertNull($id);
    }

}
