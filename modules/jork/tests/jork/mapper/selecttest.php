<?php


class JORK_Mapper_SelectTest extends Kohana_Unittest_TestCase {


    public function testFrom() {
        $jork_query = new JORK_Query_Select;
        $jork_query->from('Model_User user', 'Model_Topic topic');
        $mapper = JORK_Mapper_Select::for_query($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->tables, array(
            array('t_users', 't_users_0'),
            //array('user_contact_info', 'user_contact_info_0'),
            array('t_topics', 't_topics_0')
        ));
    }

    public function testFromImplRoot() {
        $jork_query = new JORK_Query_Select;
        $jork_query->from('Model_User');
        $mapper = JORK_Mapper_Select::for_query($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->columns, array(
            array('t_users_0.id', 't_users_0_id'), array('t_users_0.name', 't_users_0_name')
            , array('t_users_0.password', 't_users_0_password')
            , array('t_users_0.created_at', 't_users_0_created_at')
            , array('user_contact_info_0.email', 'user_contact_info_0_email')
            , array('user_contact_info_0.phone_num', 'user_contact_info_0_phone_num')
        ));
        $this->assertEquals($db_query->tables, array(
            array('t_users', 't_users_0'),
            //array('user_contact_info', 'user_contact_info_0')
        ));
    }

    public function testSelectImplRoot() {
        $jork_query = new JORK_Query_Select;
        $jork_query->from('Model_Category');
        $mapper = JORK_Mapper_Select::for_query($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->columns, array(
            array('t_categories_0.id', 't_categories_0_id')
            , array('t_categories_0.c_name', 't_categories_0_c_name')
            , array('t_categories_0.moderator_fk', 't_categories_0_moderator_fk')
            , array('t_categories_0.created_at', 't_categories_0_created_at')
            , array('t_categories_0.creator_fk', 't_categories_0_creator_fk')
            , array('t_categories_0.modified_at', 't_categories_0_modified_at')
            , array('t_categories_0.modifier_fk', 't_categories_0_modifier_fk')
        ));
        $this->assertEquals($db_query->tables, array(
            array('t_categories', 't_categories_0')
        ));
        $jork_query->select('id');
        $mapper = JORK_Mapper_Select::for_query($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->columns, array(
            array('t_categories_0.id', 't_categories_0_id')
        ));
    }

    public function testSelectPropChain() {
        $jork_query = new JORK_Query_Select;
        $jork_query->select('topic')->from('Model_Post');
        $mapper = JORK_Mapper_Select::for_query($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->columns, array(
            array('t_topics_0.id', 't_topics_0_id')
            , array('t_topics_0.name', 't_topics_0_name')
            , array('t_topics_0.created_at', 't_topics_0_created_at')
            , array('t_topics_0.creator_fk', 't_topics_0_creator_fk')
            , array('t_topics_0.modified_at', 't_topics_0_modified_at')
            , array('t_topics_0.modifier_fk', 't_topics_0_modifier_fk')
            , array('t_posts_0.id', 't_posts_0_id')
        ));
        $this->assertEquals($db_query->tables, array(
            array('t_posts', 't_posts_0')
        ));
        $this->assertEquals($db_query->joins, array(
            array(
                'table' => array('t_topics', 't_topics_0'),
                'type' => 'LEFT',
                'conditions' => array(
                    new DB_Expression_Binary('t_posts_0.topic_fk', '=', 't_topics_0.id')
                )
            )
        ));
    }

    public function testSelectManyToOne() {
        $jork_query = new JORK_Query_Select;
        $jork_query->select('topic', 'topic.creator')->from('Model_Topic topic');
        $mapper = JORK_Mapper_Select::for_query($jork_query);
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
                    new DB_Expression_Binary('t_topics_0.creator_fk', '=', 't_users_0.id')
                )
            ),
            array(
                'table' => array('user_contact_info', 'user_contact_info_0'),
                'type' => 'LEFT',
                'conditions' => array(
                    new DB_Expression_Binary('t_users_0.id', '=', 'user_contact_info_0.user_fk')
                )
            )
        ));
    }

    public function testSelectManyToOne2() {
        $jork_query = new JORK_Query_Select;
        $jork_query->select('post', 'post.topic.creator')
                ->from('Model_Post post');
        $mapper = JORK_Mapper_Select::for_query($jork_query);
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
                    new DB_Expression_Binary('t_posts_0.topic_fk', '=', 't_topics_0.id')
                )
            ),
            array(
                'table' => array('t_users', 't_users_0'),
                'type' => 'LEFT',
                'conditions' => array(
                    new DB_Expression_Binary('t_topics_0.creator_fk', '=', 't_users_0.id')
                )
            ),
            array(
                'table' => array('user_contact_info', 'user_contact_info_0'),
                'type' => 'LEFT',
                'conditions' => array(
                    new DB_Expression_Binary('t_users_0.id', '=', 'user_contact_info_0.user_fk')
                )
            )
           
        ));

    }

    public function testSelectManyToOneReverse() {
        $jork_query = new JORK_Query_Select;
        $jork_query->select('t', 't.posts')->from('Model_Topic t');
        $mapper = JORK_Mapper_Select::for_query($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->tables, array(
            array('t_topics', 't_topics_0')
        ));
        $this->assertEquals($db_query->joins, array(array(
            'table' => array('t_posts', 't_posts_0'),
            'type' => 'LEFT',
            'conditions' => array(
                new DB_Expression_Binary('t_topics_0.id', '=', 't_posts_0.topic_fk')
            )
        )));
        
    }

    public function testSelectOneToMany() {
        $jork_query = new JORK_Query_Select;
        $jork_query->select('posts')->from('Model_User');
        $mapper = JORK_Mapper_Select::for_query($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->tables, array(
            array('t_users', 't_users_0')
        ));
        $this->assertEquals($db_query->joins, array(
            array(
                'table' => array('t_posts', 't_posts_0'),
                'type' => 'LEFT',
                'conditions' => array(
                    new DB_Expression_Binary('t_users_0.id', '=', 't_posts_0.user_fk')
                )
            )
        ));
    }

    public function testSelectOneToManyReverse() {
        $jork_query = new JORK_Query_Select;
        $jork_query->select('author')->from('Model_Post');
        $mapper = JORK_Mapper_Select::for_query($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->tables, array(
            array('t_posts', 't_posts_0')
        ));
        $this->assertEquals($db_query->joins, array(
            array(
                'table' => array('t_users', 't_users_0'),
                'type' => 'LEFT',
                'conditions' => array(
                    new DB_Expression_Binary('t_posts_0.user_fk', '=', 't_users_0.id')
                )
            ),
            array(
                'table' => array('user_contact_info', 'user_contact_info_0'),
                'type' => 'LEFT',
                'conditions' => array(
                    new DB_Expression_Binary('t_users_0.id', '=', 'user_contact_info_0.user_fk')
                )
            )
        ));

    }

    public function testOneToOne() {
        $jork_query = new JORK_Query_Select;
        $jork_query->select('moderator')->from('Model_Category');
        $mapper = JORK_Mapper_Select::for_query($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->tables, array(
            array('t_categories', 't_categories_0')
        ));
        $this->assertEquals($db_query->joins, array(
            array(
                'table' => array('t_users', 't_users_0'),
                'type' => 'LEFT',
                'conditions' => array(
                    new DB_Expression_Binary('t_categories_0.moderator_fk', '=', 't_users_0.id')
                )
            ),
            array(
                'table' => array('user_contact_info', 'user_contact_info_0'),
                'type' => 'LEFT',
                'conditions' => array(
                    new DB_Expression_Binary('t_users_0.id', '=', 'user_contact_info_0.user_fk')
                )
            )
        ));
    }

    public function testOneToOneReverse() {
        $jork_query = new JORK_Query_Select;
        $jork_query->select('moderated_category')->from('Model_User');
        $mapper = JORK_Mapper_Select::for_query($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->tables, array(
            array('t_users', 't_users_0')
        ));
        $this->assertEquals($db_query->joins, array(
            array(
                'table' => array('t_categories', 't_categories_0'),
                'type' => 'LEFT',
                'conditions' => array(
                    new DB_Expression_Binary('t_users_0.id', '=', 't_categories_0.moderator_fk')
                )
            )
        ));
    }


    public function testProjection() {
        $jork_query = JORK::select('topic.creator{id,name,posts}')->from('Model_Topic topic');
        $mapper = JORK_Mapper_Select::for_query($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->tables, array(
            array('t_topics', 't_topics_0')
        ));
        $this->assertEquals($db_query->joins, array(
            array(
                'table' => array('t_users', 't_users_0'),
                'type' => 'LEFT',
                'conditions' => array(
                    new DB_Expression_Binary('t_topics_0.creator_fk', '=', 't_users_0.id')
                )
            ),
            array(
                'table' => array('user_contact_info', 'user_contact_info_0'),
                'type' => 'LEFT',
                'conditions' => array(
                    new DB_Expression_Binary('t_users_0.id', '=', 'user_contact_info_0.user_fk')
                )
            ),
            array(
                'table' => array('t_posts', 't_posts_0'),
                'type' => 'LEFT',
                'conditions' => array(
                    new DB_Expression_Binary('t_users_0.id', '=', 't_posts_0.user_fk')
                )
            )
        ));
    }

    public function testOrderBy() {
        $jork_query = JORK::from('Model_Post post')->order_by('post.created_at');
        $mapper = JORK_Mapper_Select::for_query($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->order_by, array(
            array(
                'column' => 't_posts_0.created_at',
                'direction' => 'ASC'
            )
        ));
    }

    public function testForQuery() {
        $jork_query = JORK::from('Model_Post');
        $mapper = JORK_Mapper_Select::for_query($jork_query);
        $this->assertTrue($mapper instanceof JORK_Mapper_Select_ImplRoot);
        
        $jork_query = JORK::from('Model_Post post');
        $mapper = JORK_Mapper_Select::for_query($jork_query);
        $this->assertTrue($mapper instanceof JORK_Mapper_Select_ExplRoot);

    }

    public function testGroupBy() {
        $jork_query = JORK::select(DB::expr('count({post.id})'), 'post.author')
                ->from('Model_Post post')
                ->group_by('post.author.name');
        $mapper = JORK_Mapper_Select::for_query($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->group_by, array('t_users_0.name'));
    }

}