<?php


class JORK_Adapter_Test extends Kohana_Unittest_TestCase {

    public function testMapEntity() {
        $jork_query = JORK::from('Model_User user');
        $db_query = JORK::adapter()->map_select($jork_query);
        $this->assertTrue($db_query instanceof DB_Query_Select);
        $this->assertEquals(count($db_query->columns), 1);
        $this->assertEquals($db_query->columns[0], DB::expr('*'));

        $this->assertEquals($db_query->tables, array(array('users', 'user')));

        $jork_query = JORK::from('Model_User');
        $db_query = JORK::adapter()->map_select($jork_query);
        $this->assertEquals($db_query->tables, array('users'));
    }

    public function testMapPropertyChain() {
        $jork_query = JORK::from('Model_User')->join('posts');
        $db_query = JORK::adapter()->map_select($jork_query);
        $this->assertEquals($db_query->joins[0], array(
            'table' => 'posts',
            'type' => 'INNER',
            'conditions' => array(
                array('users.id', '=', 'posts.user_fk')
            )
        ));

        $jork_query = JORK::from('Model_User')->join('posts.category');
        $db_query = JORK::adapter()->map_select($jork_query);
        $this->assertEquals($db_query->joins[0], array(
            'table' => 'posts',
            'type' => 'INNER',
            'conditions' => array(
                array('users.id', '=', 'posts.user_fk')
            )
        ));
    }
}