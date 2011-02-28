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

    public function testExecDelete() {
        DB::delete('users')->where('id', '=', DB::esc(1))->exec('postgres');
        $result = pg_query('select count(1) cnt from users');
        $row = pg_fetch_assoc($result);
        $this->assertEquals(1, $row['cnt']);
    }

    public function testExecUpdate() {
        DB::update('users')->values(array('name' => 'user2_mod'))
                ->where('id', '=', DB::esc(2))->exec('postgres');

        $result = pg_query('select name from users where id = 2');
        $row = pg_fetch_assoc($result);
        $this->assertEquals('user2_mod', $row['name']);
    }

}
