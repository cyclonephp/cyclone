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
        $result = DB::select('id', 'name')->from('user')->prepare()->exec();
        $this->assertEquals(2, count($result));
    }

    /**
     * @expectedException DB_Exception
     */
    public function testExecSelectFailure() {
        $result = DB::select()->from('user')->prepare()->exec();
    }

    public function testPreparedResult() {
        $stmt = DB::connector()->db_conn->prepare('select id, name from cy_user');
        $stmt->execute();
        $stmt->store_result();
        $result = new DB_Query_Result_Prepared_MySQLi($stmt, DB::select('id', 'name')->from('user'));
        $this->assertEquals(2, count($result));

        $exp = array(
            array('id' => 1, 'name' => 'user1'),
            array('id' => 2, 'name' => 'user2')
        );
        $idx = 0;
        foreach ($result as $key => $row) {
            $this->assertEquals($idx, $key);
            $this->assertEquals($exp[$idx]['id'], $row['id']);
            ++$idx;
        }

        $idx = 0;
        foreach ($result as $key => $row) {
            $this->assertEquals($idx, $key);
            $this->assertEquals($exp[$idx]['id'], $row['id']);
            ++$idx;
        }
    }

    public function testPreparedResultIndexBy() {
        $stmt = DB::connector()->db_conn->prepare('select id, name from cy_user');
        $stmt->execute();
        $stmt->store_result();
        $result = new DB_Query_Result_Prepared_MySQLi($stmt, DB::select('id', 'name')->from('user'));
        $result->index_by('id');
        $this->assertEquals(2, count($result));

        $exp = array(
            1 => array('id' => 1, 'name' => 'user1'),
            2 => array('id' => 2, 'name' => 'user2')
        );
        $idx = 1;
        foreach ($result as $key => $row) {
            $this->assertEquals($idx, $key);
            $this->assertEquals($exp[$idx]['id'], $row['id']);
            ++$idx;
        }

        $idx = 1;
        foreach ($result as $key => $row) {
            $this->assertEquals($idx, $key);
            $this->assertEquals($exp[$idx]['id'], $row['id']);
            ++$idx;
        }
    }

    public function testInsert() {
        $insert_id = DB::insert('user')->values(array('name' => 'user3'))
                ->prepare()->exec();
        $this->assertEquals(3, $insert_id);
    }

    public function testUpdate() {
        $aff_rows = DB::update('user')->values(array('name' => 'u'))
                ->prepare()->exec();
        $this->assertEquals(2, $aff_rows);
    }

    public function testDelete() {
        $aff_rows = DB::delete('user')->prepare()->exec();
        $this->assertEquals(2, $aff_rows);
    }

    public function testParamInt() {
        $result = DB::select('name')->from('user')->where('id', '=', DB::param())
                ->prepare()->param(2)->exec();

        $this->assertEquals(1, count($result));
        
        $result = DB::select('name')->from('user')->where('id', '=', DB::param())
                ->where('id', '=', DB::param())->prepare()->param(1)->param(2)->exec();

        $this->assertEquals(0, count($result));
    }

    public function testParamString() {
        $result = DB::select('name')->from('user')->where('name', '=', DB::param())
                ->prepare()->param('user1')->exec();

        $this->assertEquals(1, count($result));

        $result = DB::select('name')->from('user')->where('name', '=', DB::param())
                ->where('name', '=', DB::param())->prepare()
                ->param('user1')->param('user2')->exec();

        $this->assertEquals(0, count($result));
    }

    public function testParamBoolean() {
        $result = DB::select('name')->from('user')->where('id', '=', DB::param())
                ->prepare()->param(TRUE)->exec();

        $this->assertEquals(1, count($result));

    }

    /**
     * @expectedException DB_Exception
     */
    public function testParamArray() {
        $result = DB::select('name')->from('user')->where('id', '=', DB::param())
                ->prepare()->param(array())->exec();
    }
}