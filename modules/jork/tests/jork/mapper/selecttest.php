<?php


class JORK_Mapper_SelectTest extends Kohana_Unittest_TestCase {

    public function testFrom() {
        $jork_query = new JORK_Query_Select;
        $jork_query->from('Model_User user', 'Model_Topic topic');
        $mapper = new JORK_Mapper_Select($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->tables, array(
            array('t_users', 't_users_0'),
            array('user_contact_info', 'user_contact_info_0'),
            array('t_topics', 't_topics_0')
        ));
    }

    public function testFromImplRoot() {
        $jork_query = new JORK_Query_Select;
        $jork_query->from('Model_User');
        $mapper = new JORK_Mapper_Select($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->tables, array(
            array('t_users', 't_users_0'),
            array('user_contact_info', 'user_contact_info_0')
        ));
    }

    public function testSelectImplRoot() {
        $jork_query = new JORK_Query_Select;
        $jork_query->from('Model_Category');
        $mapper = new JORK_Mapper_Select($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->columns, array(
            't_categories_0.id', 't_categories_0.c_name'
            , 't_categories_0.created_at', 't_categories_0.creator_fk'
            , 't_categories_0.modified_at', 't_categories_0.modifier_fk'
        ));
        $this->assertEquals($db_query->tables, array(
            array('t_categories', 't_categories_0')
        ));
        $jork_query->select('id');
        $mapper = new JORK_Mapper_Select($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->columns, array(
            't_categories_0.id'
        ));
    }

    public function testSelectPropChain() {
        $jork_query = new JORK_Query_Select;
        $jork_query->select('topic')->from('Model_Post');
        $mapper = new JORK_Mapper_Select($jork_query);
        list($db_query, ) = $mapper->map();
        //print_r($db_query->columns);
        $this->assertEquals($db_query->tables, array(
            array('t_posts', 't_posts_0')
        ));
        /*$this->assertEquals($db_query->joins, array(
            array(
                'type' => 'LEFT',
                'table' => array('t_topics', 't_topics_0'),
                'conditions' => array(
                    array('t_categories_0.id', )
                )
            )
        ));*/
    }

    public function testSelectManyToOne() {
        $jork_query = new JORK_Query_Select;
        $jork_query->select('topic', 'topic.creator')->from('Model_Topic topic');
        $mapper = new JORK_Mapper_Select($jork_query);
        list($db_query, ) = $mapper->map();
        //print_r($db_query->tables);
        $this->assertEquals($db_query->tables, array(
            array('t_topics', 't_topics_0')
        ));
        //print_r($db_query->joins);
        $this->assertEquals($db_query->joins, array(
            array(
                'table' => array('t_users', 't_users_0'),
                'type' => 'LEFT',
                'conditions' => array(
                    array('t_topics_0.creator_fk', '=', 't_users_0.id')
                )
            )
        ));
    }

    public function testSelectManyToOne2() {
        $jork_query = new JORK_Query_Select;
        $jork_query->select('post', 'post.topic.creator')
                ->from('Model_Post post');
        $mapper = new JORK_Mapper_Select($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->tables, array(
            array('t_posts', 't_posts_0')
        ));
        //print_r($db_query->joins);
        $this->assertEquals($db_query->joins, array(
             array(
                'table' => array('t_topics', 't_topics_0'),
                'type' => 'LEFT',
                'conditions' => array(
                    array('t_posts_0.topic_fk', '=', 't_topics_0.id')
                )
            ),
            array(
                'table' => array('t_users', 't_users_0'),
                'type' => 'LEFT',
                'conditions' => array(
                    array('t_topics_0.creator_fk', '=', 't_users_0.id')
                )
            )
           
        ));

    }

    public function testSelectManyToOneReverse() {
        $jork_query = new JORK_Query_Select;
        $jork_query->select('t', 't.posts')->from('Model_Topic t');
        $mapper = new JORK_Mapper_Select($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->tables, array(
            array('t_topics', 't_topics_0')
        ));
        $this->assertEquals($db_query->joins, array(array(
            'table' => array('t_posts', 't_posts_0'),
            'type' => 'LEFT',
            'conditions' => array(
                array('t_topics_0.id', '=', 't_posts_0.topic_fk')
            )
        )));
        
    }

    public function testSelectOneToMany() {
        $jork_query = new JORK_Query_Select;
        $jork_query->select('posts')->from('Model_User');
        $mapper = new JORK_Mapper_Select($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->tables, array(
            array('t_users', 't_users_0')
        ));
        $this->assertEquals($db_query->joins, array(
            array(
                'table' => array('t_posts', 't_posts_0'),
                'type' => 'LEFT',
                'conditions' => array(
                    array('t_users_0.id', '=', 't_posts_0.user_fk')
                )
            )
        ));
    }

    
}