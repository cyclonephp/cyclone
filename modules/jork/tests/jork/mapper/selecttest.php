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

    public function testFromImpl() {
        $jork_query = new JORK_Query_Select;
        $jork_query->from('Model_User');
        $mapper = new JORK_Mapper_Select($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->tables, array(array('t_users', 't_users_0')));
    }

    public function testSelectFrom() {
        $jork_query = new JORK_Query_Select;
        $jork_query->from('Model_Category');
        $mapper = new JORK_Mapper_Select($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->columns, array());
    }

    
}