<?php


class JORK_Result_MapperTest extends Kohana_Unittest_TestCase {

    public function  setUp() {
        $sql = file_get_contents(MODPATH.'jork/tests/testdata.sql');
        DB::inst('jork_test')->exec_custom($sql);
        DB::inst('jork_test')->disconnect();
        DB::inst('jork_test')->connect();
        DB::select()->from('t_posts')->exec('jork_test');
    }

    public function testConfig() {
        Config::inst()->get('jork.show_sql');
        DB::inst('jork_test')->exec_custom('select 1');
    }

    public function testImplRoot() {
        $jork_query = new JORK_Query_Select;
        $jork_query->from('Model_Topic');
        $mapper = new JORK_Mapper_Select($jork_query);
        list($db_query, $mappers) = $mapper->map();

        $resultset = array(
            array('t_topics_0_id' => '1', 't_topics_0_name' => 'hello'
                , 't_topics_0_created_at' => '2011-01-05', 't_topics_0_creator_fk' => 1, 't_topics_0_modified_at' => '2011-01-05', 't_topics_0_modifier_fk' => 1 )
            , array('t_topics_0_id' => '1', 't_topics_0_name' => 'hello'
                , 't_topics_0_created_at' => '2011-01-05', 't_topics_0_creator_fk' => 1, 't_topics_0_modified_at' => '2011-01-05', 't_topics_0_modifier_fk' => 1 )
        );
        foreach ($resultset as $idx => $row) {
            $topic = $mappers[NULL]->map_row($resultset[0]);
            $this->assertEquals(count($topic), 2);
            $this->assertEquals($topic[1], (boolean) ! $idx);
            $this->assertTrue($topic[0] instanceof  Model_Topic);
            $this->assertEquals($topic[0]->id, 1);
            $this->assertEquals($topic[0]->name, 'hello');
        }
    }

    public function testFirstFromDB() {
        $result = JORK::from('Model_User')->exec('jork_test');
    }
}