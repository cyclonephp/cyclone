<?php


class JORK_Mapping_Test extends Kohana_Unittest_TestCase {

    public function testMapEntity() {
        $jork_query = JORK::from('Model_User user');
        $mapper = new JORK_Mapper_Select($jork_query);
        list($db_query, $metadata) = $mapper->map();
        $this->assertEquals($db_query->tables
                , array(array('t_users', 't_users_1')));
    }

    public function testMapJoin() {
        $jork_query = JORK::from('Model_User user')
            ->join('posts.topic user_topics');
            //->join('posts.topics.creator topic_creator');
        $mapper = new JORK_Mapper_Select($jork_query);
        list($db_query, $metadata) = $mapper->map();
        $this->assertEquals($db_query->joins, array(
            array(
                'table' => array('t_posts', 't_posts_1')
                , 'type' => 'INNER'
                , 'conditions' => array(
                    array('t_user_1.id', '=', 't_posts_1.user_fk')
                )
            )
        ));
    }

}