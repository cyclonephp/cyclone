<?php


class JORK_Result_MapperTest extends JORK_DbTest {

    public function testConfig() {
        Config::inst()->get('jork.show_sql');
        DB::inst('jork_test')->exec_custom('select 1');
    }

    public function testImplRoot() {
        $jork_query = new JORK_Query_Select;
        $jork_query->from('Model_Topic');
        $mapper = JORK_Mapper_Select::for_query($jork_query);
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
        $this->assertEquals(4, count($result));
        $idx = 1;
        foreach ($result as $user) {
            $this->assertEquals($user->id, $idx);
            $this->assertEquals($user->name, "user$idx");
            ++$idx;
        }
    }

    public function testManyCompJoin() {
        $result = JORK::from('Model_User')->with('posts.topic')->exec('jork_test');
        $idx = 1;
        foreach ($result as $user) {
            $this->assertTrue($user instanceof  Model_User);
            $this->assertTrue($user->posts instanceof ArrayObject);
            if ($idx == 1) {
                $this->assertTrue($user->posts[1] instanceof Model_Post);
                $this->assertEquals(1, $user->posts[1]->id);
                $this->assertEquals('t 01 p 01', $user->posts[1]->name);

                $this->assertTrue($user->posts[1]->topic instanceof Model_Topic);
                $this->assertEquals(1, $user->posts[1]->topic->id);

                $this->assertEquals(3, $user->posts[3]->id);
                $this->assertEquals('t 02 p 01', $user->posts[3]->name);
            }
            ++$idx;
        }
    }


    /**
     * @dataProvider providerOuterJoinEmptyRowSkip
     */
    public function testOuterJoinEmptyRowSkip($topic_idx, $post_count) {
        $result = JORK::from('Model_Topic')->with('posts')->exec('jork_test');
        $this->assertEquals(4, count($result));
        $idx = 1;
        foreach ($result as $topic) {
            $this->assertTrue($topic instanceof Model_Topic);
            $this->assertEquals($idx, $topic->id);
            ++$idx;
        }
        $this->assertEquals($topic_idx + 1, $result[$topic_idx]->id);
        $this->assertEquals($post_count, count($result[$topic_idx]->posts));
    }

    public function providerOuterJoinEmptyRowSkip() {
        return array(
            array(0, 2),
            array(1, 1),
            array(2, 0),
            array(3, 1)
        );
    }
}