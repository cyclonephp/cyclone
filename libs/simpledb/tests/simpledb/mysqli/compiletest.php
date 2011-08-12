<?php

require_once DOCROOT.'libs/simpledb/tests/simpledb/mysqli/dbtest.php';

class SimpleDB_Mysqli_CompileTest extends SimpleDB_Mysqli_DbTest {

    public function testCompileParams() {
        $sql = DB::select('name')->from('user')->where('id', '=', DB::param())
                ->compile();
        $this->assertEquals("SELECT `name` FROM (`cy_user`) WHERE `id` = ?", $sql);
    }

    public function testCompileInsert() {
        $query = DB::insert('user')->values(array(
            'name' => 'user'
            , 'email' => 'user@example.com'));
        $this->assertEquals("INSERT INTO `cy_user` (`name`, `email`) VALUES ('user', 'user@example.com')"
                , $query->compile());
    }

    public function testCompileUpdate() {
        $query = DB::update('user')->values(array('name' => NULL, 'email' => 'ebence88@gmail.com'))
                ->where('id', '=', DB::esc(1))->limit(10);

        $this->assertEquals("UPDATE `cy_user` SET `name` = NULL, `email` = 'ebence88@gmail.com' WHERE `id` = '1' LIMIT 10",
                $query->compile());
    }

    public function testCompileDelete() {
        $query = DB::delete('user')->where('name', 'like', '%crys%')->limit(10);

        $this->assertEquals("DELETE FROM `cy_user` WHERE `name` like `%crys%` LIMIT 10"
                , $query->compile());
    }

    public function testCompileSelect() {
        $query = DB::select_distinct('id', 'name', array(DB::select(DB::expr('count(1)'))->from('posts')
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
                'SELECT DISTINCT `id`, `name`, ((SELECT count(1) FROM (`cy_posts`) WHERE `cy_posts`.`author_fk` = `cy_user`.`id`)) AS `post_count` FROM (`cy_users`) LEFT JOIN `cy_groups` ON `cy_users`.`group_fk` = `cy_group`.`id` WHERE `2` = `1` + `1` AND `4` = 2 + 2 GROUP BY `id` HAVING `2` = `2` ORDER BY `id` DESC LIMIT 20 OFFSET 10');
    }

    public function testSet() {
        $sql = DB::select()->from('user')->where('col', 'IN', DB::expr(array(1, 2)))->compile();
        $this->assertEquals($sql, "SELECT * FROM (`cy_user`) WHERE `col` IN ('1', '2')");
    }

    public function testStarEscaping() {
        $sql = DB::select('user.*')->from('user')->compile();
        $this->assertEquals($sql, 'SELECT `cy_user`.* FROM (`cy_user`)');
    }

    public function testNullValues() {
        $sql = DB::select()->from('user')->where('id', 'IS', null)->compile();
        $this->assertEquals($sql, "SELECT * FROM (`cy_user`) WHERE `id` IS NULL");
    }

    public function testPrefix(){
        $query = DB::select('user.id','name')
                ->from('user')
                ->join('posts')
                ->on('posts.user_fk', '=', 'user.id')
                ->where('user.registered_at', '>', DB::esc('2010-01-01'));
        $sql = $query->compile();
        $this->assertEquals("SELECT `cy_user`.`id`, `name` FROM (`cy_user`) INNER JOIN `cy_posts` ON `cy_posts`.`user_fk` = `cy_user`.`id` WHERE `cy_user`.`registered_at` > '2010-01-01'"
                            , $sql);

        $query = DB::select('u.id')
                ->from(array('user','u'));
        $sql = $query->compile();
        $this->assertEquals("SELECT `u`.`id` FROM (`cy_user` `u`)", $sql);

        $query = DB::select('u.id','t.id')
                ->from(array('user','u'))
                ->from(array('temp','t'));
        $sql = $query->compile();
        $this->assertEquals("SELECT `u`.`id`, `t`.`id` FROM (`cy_user` `u`, `cy_temp` `t`)", $sql);
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
        $this->assertEquals("SELECT `u`.`id`, `u`.`name` FROM (`cy_user` `u`) UNION ALL SELECT `azon`, `nev` FROM (`cy_tablanev`)",$sql);

        $union_query = DB::select('azon','nev')
                ->from('tablanev');
        $query = DB::select('u.id','u.name')
                ->from(array('user','u'))
                ->union($union_query, FALSE);
        $sql = $query->compile();
        $this->assertEquals("SELECT `u`.`id`, `u`.`name` FROM (`cy_user` `u`) UNION SELECT `azon`, `nev` FROM (`cy_tablanev`)",$sql);

        $union_query_all = DB::select('azon','nev')
                ->from('tablanev');
        $union_query = DB::select('column_name1','column_name2')
                ->from('table_name');
        $query = DB::select('u.id','u.name')
                ->from(array('user','u'))
                ->union($union_query_all, TRUE)
                ->union($union_query, FALSE);
        $sql = $query->compile();
        $this->assertEquals("SELECT `u`.`id`, `u`.`name` FROM (`cy_user` `u`) UNION ALL SELECT `azon`, `nev` FROM (`cy_tablanev`) UNION SELECT `column_name1`, `column_name2` FROM (`cy_table_name`)",$sql);
    }

    public function testHints(){
        $query = DB::select('u.name')
                ->from(array('user','u'))
                ->hint('INDEX (some_index)');
        $sql = $query->compile();
        $this->assertEquals("SELECT `u`.`name` FROM (`cy_user` `u`) USE INDEX (some_index)", $sql);

        $query = DB::select('u.name')
                ->from(array('user','u'))
                ->hint('INDEX (index1)')
                ->hint('IGNORE INDEX (index1) FOR ORDER BY')
                ->hint('IGNORE INDEX (index1) FOR GROUP BY');
        $sql = $query->compile();
        $this->assertEquals("SELECT `u`.`name` FROM (`cy_user` `u`) USE INDEX (index1) IGNORE INDEX (index1) FOR ORDER BY IGNORE INDEX (index1) FOR GROUP BY"
                , $sql);
    }


}