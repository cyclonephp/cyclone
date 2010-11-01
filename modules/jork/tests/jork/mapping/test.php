<?php


class JORK_Mapping_Test extends Kohana_Unittest_TestCase {

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

        $jork_query = JORK::from('Model_User user')->join('posts.topic');
        $db_query = JORK::adapter()->map_select($jork_query);
        $this->assertEquals($db_query->joins[0], array(
            'table' => 'posts',
            'type' => 'INNER',
            'conditions' => array(
                array('user.id', '=', 'posts.user_fk')
            )
        ));
        $this->assertEquals($db_query->joins[1], array(
            'table' => 'topics',
            'type' => 'INNER',
            'conditions' => array(
                array('posts.topic_fk', '=', 'topics.id')
            )
        ));
    }


    public function testMapPropertyChainManyToMany() {
        $jork_query = JORK::from('Model_User')->join('posts.topic.categories');
        $db_query = JORK::adapter()->map_select($jork_query);
        $this->assertEquals($db_query->joins, array(
            array(
                'table' => 'posts',
                'type' => 'INNER',
                'conditions' => array(
                    array('users.id', '=', 'posts.user_fk')
                )
            ),
            array(
                'table' => 'topics',
                'type' => 'INNER',
                'conditions' => array(
                    array('posts.topic_fk', '=', 'topics.id')
                )
            ),
            array(
                'table' => 'categories_topics',
                'type' => 'INNER',
                'conditions' => array(
                    array('topics.id', '=', 'categories_topics.topic_fk')
                )
            ),
            array(
                'table' => 'categories',
                'type' => 'INNER',
                'conditions' => array(
                    array('categories_topics.category_fk', '=', 'categories.id')
                )
            )
        ));
    }
}