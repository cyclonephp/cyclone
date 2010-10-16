<?php


class SimpleDB_Test extends Kohana_Unittest_TestCase {


    public function testInstance() {
        $inst = DB::inst();
        $this->assertTrue($inst instanceof DB_Adapter_Mysqli);
        $inst->disconnect();
    }

    public function testQueryFactory() {
        $query = DB::select();
        $this->assertEquals($query->columns, array(DB::expr('*')));

        $query = DB::update('user');
        $this->assertEquals($query->table, 'user');

        $query = DB::insert('user');
        $this->assertEquals($query->table, 'user');

        $query = DB::delete('user');
        $this->assertEquals($query->table, 'user');
    }

    public function testExpressionFactory() {
        $expr = DB::expr('a', '=', 'b');
        $this->assertTrue($expr instanceof  DB_Expression_Binary);

        $expr = DB::expr('exists', DB::select());
        $this->assertTrue($expr instanceof  DB_Expression_Unary);
    }

    public function testQuerySelect() {
        $query = DB::select()->from('user')
                ->join('group')->on('user.group_fk', '=', 'group.id')
                ->where('exists', DB::select()->from('user'))
                ->group_by('id', 'name')
                ->having('hello', '=', 'world')
                ->offset(2)
                ->limit(10);
    }

    public function testQueryDelete() {
        $query = DB::delete('user')->where('id', '=', 15);
    }

    public function testCompileInsert() {
        $query = DB::insert('user')->values(array(
            'name' => 'user'
            , 'email' => 'user@example.com'));

        $this->assertEquals("INSERT INTO `user` (`name`, `email`) VALUES ('user', 'user@example.com')"
                , $query->compile());
    }

    public function testCompileUpdate() {
        $query = DB::update('user')->values(array('name' => 'crystal', 'email' => 'ebence88@gmail.com'))
                ->where('id', '=', DB::esc(1));

        $this->assertEquals("UPDATE `user` SET `name` = 'crystal', `email` = 'ebence88@gmail.com' WHERE `id` = '1'",
                $query->compile());
    }

    public function testCompileDelete() {
        $query = DB::delete('user')->where('name', 'like', '%crys%')->limit(10);

        $this->assertEquals("DELETE FROM `user` WHERE `name` like `%crys%` LIMIT 10"
                , $query->compile());
    }

    public function testCompileSelect() {
        $query = DB::select('id', 'name', array(DB::select(DB::expr('count(1)'))->from('posts')
                ->where('posts.author_fk', '=', 'user.id'), 'post_count'))->from('users')
                ->left_join('groups')->on('users.group_fk', '=', 'group.id')
                ->where(2, '=', DB::expr(1, '+', 1))
                ->where(4, '=', DB::expr('2 + 2'))
                ->group_by('id')
                ->having('2', '=', 2)
                ->order_by('id', 'DESC')
                ->offset(10)
                ->limit(20);
                ;

        $this->assertEquals($query->compile(), 
                'SELECT `id`, `name`, (SELECT count(1) FROM `posts` WHERE `posts`.`author_fk` = `user`.`id`) AS `post_count` FROM `users` LEFT JOIN `groups` ON `users`.`group_fk` = `group`.`id` WHERE `2` = `1` + `1` AND `4` = 2 + 2 GROUP BY `id` HAVING `2` = `2` ORDER BY `id` DESC LIMIT `20` OFFSET `10`');
    }

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

    public function testSet() {
        $sql = DB::select()->from('user')->where('col', 'IN', DB::expr(array(1, 2)))->compile();
        $this->assertEquals($sql, "SELECT * FROM `user` WHERE `col` IN ('1', '2')");
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

    public function testStarEscaping() {
        $sql = DB::select('user.*')->from('user')->compile();
        $this->assertEquals($sql, 'SELECT `user`.* FROM `user`');
    }

    public function testNullValues() {
        $sql = DB::select()->from('user')->where('id', 'IS', null)->compile();
        $this->assertEquals($sql, "SELECT * FROM `user` WHERE `id` IS NULL");
    }

    public function setUp() {
        DB::query('truncate user')->exec();
        $names = array('user1', 'user2');
        $insert = DB::insert('user');
        foreach ($names as $name) {
            $insert->values(array('name' => $name));
        }
        $insert->exec();
    }

    public function tearDown() {
        DB::clear_connections();
    }

}