<?php


class JORK_Result_MapperTest extends Kohana_Unittest_TestCase {

    public function testImplRoot() {
        $jork_query = new JORK_Query_Select;
        $jork_query->from('Model_Topic');
        $mapper = new JORK_Mapper_Select($jork_query);
        list($db_query, $mappers) = $mapper->map();

        $resultset = array(
            array('t_topics_0.id' => '1', 't_topics_0.name' => 'hello'
                , 't_topics_0.created_at' => '2011-01-05', 't_topics_0.creator_fk' => 1, 't_topics_0.modified_at' => '2011-01-05', 't_topics_0.modifier_fk' => 1 )
        );
        $topic = $mappers[NULL]->map_row($resultset[0]);
        $this->assertTrue($topic[0] instanceof  Model_Topic);
        $this->assertEquals($topic[0]->id, 1);
        $this->assertEquals($topic[0]->name, 'hello');
    }
}