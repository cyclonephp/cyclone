<?php

class SimpleDB_Mysqli_ExecTest extends SimpleDB_MySQLi_DbTest {

    /**
     *
     * @expectedException DB_Exception
     */
    public function testExecUpdate() {
        $affected = DB::update('user')->values(array('name' => 'crystal88_'))->exec();
        $this->assertEquals($affected, 2);
        DB::update('users')->values(array('name' => 'crystal88_'))->exec();
    }

    /**
     *
     * @expectedException DB_Exception
     */
    public function testExecDelete() {
        $affected = DB::delete('user')->exec();
        $this->assertEquals($affected, 2);
        DB::delete('users')->exec();
    }

    /**
     *
     * @expectedException DB_Exception
     */
    public function testExecInsert() {
        $insert_id = DB::insert('user')->values(array('name' => 'crystal'))->exec();
        $this->assertEquals(3, $insert_id);
        $insert_id = DB::insert('user')->values(array('name' => 'crystal'))
                ->values(array('name' => 'crystal'))->exec();
        $this->assertEquals(4, $insert_id);
        DB::insert('users')->values(array('name' => 'crystal'))->exec();
    }

    public function testExecSelect() {
        $names = array('user1', 'user2');
        $result = DB::select()->from('user')->exec();
        $this->assertTrue($result instanceof DB_Query_Result);
        $this->assertEquals(2, $result->count());
        $idx = 0;
        foreach ($result as $v) {
            $this->assertEquals($v['name'], $names[$idx++]);
        }
        $result = DB::select()->from('user')->exec();
        $result->rows('stdClass');
        $idx = 0;
        foreach ($result as $v) {
            $this->assertEquals($v->name, $names[$idx++]);
        }
        $result = DB::select()->from('user')->exec()
                ->index_by('name')->rows('stdClass');
        $idx = 0;
        foreach ($result as $k => $v) {
            $this->assertEquals($v->name, $names[$idx]);
            $this->assertEquals($k, $names[$idx++]);
        }
    }

    public function testExecMultiquery() {
        $result1 = DB::select()->from('user')->exec();
        $result2 = DB::select()->from('user')->exec();
        foreach ($result1 as $k => $v) {

        }

        DB::inst()->exec_custom('select 2');

        DB::inst()->exec_custom('drop table if exists t_posts; create table t_posts(id int);');
        DB::inst()->disconnect();
        DB::inst()->connect();
        //DB::select()->from('t_posts')->exec();
    }

    public function testExecCustom() {
        DB::inst()->exec_custom('create table if not exists tmp (id int)');
    }

    public function testAsArray() {
        $names = array('user1', 'user2');
        $result = DB::select()->from('user')->exec()->index_by('name')->rows('stdClass')->as_array();
        $this->assertEquals(count($result), 2);
        $idx = 0;
        foreach ($result as $k => $v) {
            $this->assertEquals($v->name, $names[$idx]);
            $this->assertEquals($k, $names[$idx++]);
        }
    }

    public function testCommitRollback() {
        DB::inst()->autocommit(false);
        $deleted_rows = DB::delete('user')->exec();
        $this->assertEquals($deleted_rows, 2);
        DB::inst()->rollback();
        $existing_rows = DB::select()->from('user')->exec()->count();
        $this->assertEquals($existing_rows, 2);
        $deleted_rows = DB::delete('user')->exec();
        $this->assertEquals($deleted_rows, 2);
        DB::inst()->commit();
        $existing_rows = DB::select()->from('user')->exec()->count();
        $this->assertEquals($existing_rows, 0);
    }

    public function testTransactionSuccess() {
        $tx = new DB_Transaction;
        $tx []= DB::delete('user')->limit(1);
        $tx []= DB::delete('user')->limit(1);
        $tx->exec();
    }

    public function testTransactionFailure() {
        $tx = new DB_Transaction;
        $tx []= DB::delete('user')->limit(1);
        $tx []= DB::delete('badtablename')->limit(1);
        try {
            $tx->exec();
            $failed = false;
        } catch (DB_Exception $ex) {
            $failed = true;
        }
        $this->assertTrue($failed);
        $this->assertEquals(2, DB::select()->from('user')->exec()->count());
    }
    
}