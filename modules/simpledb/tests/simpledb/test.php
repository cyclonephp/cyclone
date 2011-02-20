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
        $this->assertEquals("INSERT INTO `cy_user` (`name`, `email`) VALUES ('user', 'user@example.com')"
                , $query->compile());
    }

    public function testCompileUpdate() {
        $query = DB::update('user')->values(array('name' => 'crystal', 'email' => 'ebence88@gmail.com'))
                ->where('id', '=', DB::esc(1))->limit(10);

        $this->assertEquals("UPDATE `cy_user` SET `name` = 'crystal', `email` = 'ebence88@gmail.com' WHERE `id` = '1' LIMIT 10",
                $query->compile());
    }

    public function testCompileDelete() {
        $query = DB::delete('user')->where('name', 'like', '%crys%')->limit(10);

        $this->assertEquals("DELETE FROM `cy_user` WHERE `name` like `%crys%` LIMIT 10"
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
                'SELECT `id`, `name`, (SELECT count(1) FROM `cy_posts` WHERE `cy_posts`.`author_fk` = `cy_user`.`id`) AS `post_count` FROM `cy_users` LEFT JOIN `cy_groups` ON `cy_users`.`group_fk` = `cy_group`.`id` WHERE `2` = `1` + `1` AND `4` = 2 + 2 GROUP BY `id` HAVING `2` = `2` ORDER BY `id` DESC LIMIT 20 OFFSET 10');
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
        $this->assertEquals($sql, "SELECT * FROM `cy_user` WHERE `col` IN ('1', '2')");
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

    public function testStarEscaping() {
        $sql = DB::select('user.*')->from('user')->compile();
        $this->assertEquals($sql, 'SELECT `cy_user`.* FROM `cy_user`');
    }

    public function testNullValues() {
        $sql = DB::select()->from('user')->where('id', 'IS', null)->compile();
        $this->assertEquals($sql, "SELECT * FROM `cy_user` WHERE `id` IS NULL");
    }

    public function testPrefix(){
        $query = DB::select('user.id','name')
                ->from('user')
                ->join('posts')
                ->on('posts.user_fk', '=', 'user.id')
                ->where('user.registered_at', '>', DB::esc('2010-01-01'));
        $sql = $query->compile();
        $this->assertEquals("SELECT `cy_user`.`id`, `name` FROM `cy_user` INNER JOIN `cy_posts` ON `cy_posts`.`user_fk` = `cy_user`.`id` WHERE `cy_user`.`registered_at` > '2010-01-01'"
                            , $sql);

        $query = DB::select('u.id')
                ->from(array('user','u'));
        $sql = $query->compile();
        $this->assertEquals("SELECT `u`.`id` FROM `cy_user` `u`", $sql);

        $query = DB::select('u.id','t.id')
                ->from(array('user','u'))
                ->from(array('temp','t'));
        $sql = $query->compile();
        $this->assertEquals("SELECT `u`.`id`, `t`.`id` FROM `cy_user` `u`, `cy_temp` `t`", $sql);
        //$query = DB::select();
        //TODO validate + more test
    }

    public function testUnions(){
        $union_query_all = DB::select('azon','nev')
                ->from('tablanev');
        $query = DB::select('u.id','u.name')
                ->from(array('user','u'))
                ->union($union_query_all, TRUE);
        $sql = $query->compile();
        $this->assertEquals("SELECT `u`.`id`, `u`.`name` FROM `cy_user` `u` UNION ALL SELECT `azon`, `nev` FROM `cy_tablanev`",$sql);

        $union_query = DB::select('azon','nev')
                ->from('tablanev');
        $query = DB::select('u.id','u.name')
                ->from(array('user','u'))
                ->union($union_query, FALSE);
        $sql = $query->compile();
        $this->assertEquals("SELECT `u`.`id`, `u`.`name` FROM `cy_user` `u` UNION SELECT `azon`, `nev` FROM `cy_tablanev`",$sql);

        $union_query_all = DB::select('azon','nev')
                ->from('tablanev');
        $union_query = DB::select('column_name1','column_name2')
                ->from('table_name');
        $query = DB::select('u.id','u.name')
                ->from(array('user','u'))
                ->union($union_query_all, TRUE)
                ->union($union_query, FALSE);
        $sql = $query->compile();
        $this->assertEquals("SELECT `u`.`id`, `u`.`name` FROM `cy_user` `u` UNION ALL SELECT `azon`, `nev` FROM `cy_tablanev` UNION SELECT `column_name1`, `column_name2` FROM `cy_table_name`",$sql);
    }

    public function testHints(){
        $query = DB::select('u.name')
                ->from(array('user','u'))
                ->hint('INDEX (some_index)');
        $sql = $query->compile();
        $this->assertEquals("SELECT `u`.`name` FROM `cy_user` `u` USE INDEX (some_index)", $sql);

        $query = DB::select('u.name')
                ->from(array('user','u'))
                ->hint('INDEX (index1)')
                ->hint('IGNORE INDEX (index1) FOR ORDER BY')
                ->hint('IGNORE INDEX (index1) FOR GROUP BY');
        $sql = $query->compile();
        $this->assertEquals("SELECT `u`.`name` FROM `cy_user` `u` USE INDEX (index1) IGNORE INDEX (index1) FOR ORDER BY IGNORE INDEX (index1) FOR GROUP BY"
                , $sql);
    }

    public function setUp() {
        try {
            DB::query('truncate cy_user')->exec();
            $names = array('user1', 'user2');
            $insert = DB::insert('user');
            foreach ($names as $name) {
                $insert->values(array('name' => $name));
            }
            $insert->exec();
        } catch (Exception $ex) {
            echo $ex->getMessage().PHP_EOL;
            $this->markTestSkipped('skipping simpledb tests');
        }
    }

    public function tearDown() {
        DB::clear_connections();
    }
}