<?php


class JORK_Mapper_SelectTest extends Kohana_Unittest_TestCase {

    public function testFrom() {
        $jork_query = new JORK_Query_Select;
        $jork_query->from('Model_User user', 'Model_Topic topic');
        $mapper = new JORK_Mapper_Select($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->tables, array(
            array('t_users', 't_users_0'),
            array('t_topics', 't_topics_0')
        ));
    }

    public function testFromImplRoot() {
        $jork_query = new JORK_Query_Select;
        $jork_query->from('Model_User');
        $mapper = new JORK_Mapper_Select($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->tables, array(array('t_users', 't_users_0')));
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

        $jork_query->select('id');
        $mapper = new JORK_Mapper_Select($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->columns, array(
            't_categories_0.id'
        ));
    }

    
}