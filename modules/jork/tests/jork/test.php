<?php


class JORK_Test extends Kohana_Unittest_TestCase {

    public function setUp() {
        Config::setup();
        Kohana::$environment = Kohana::TESTING;
    }

    public function testBasicSelect() {
        $query = JORK::from('Model_User');
        $this->assertTrue($query instanceof  JORK_Query_Select);
    }

    public function testSelect() {
        $query = JORK::from('Model_User user')
            ->join('user.posts post')
            ->join('post.topic topic')
            ->join('topic.categories categories')
                ;
    }

//    public function testSimpleSelect() {
//        $result = JORK::from('Model_User user')->exec();
//        $this->assertTrue($result instanceof JORK_Query_Result);
//        foreach ($result as $user) {
//            $this->assertTrue($user instanceof Model_User);
//        }
//    }

    public function testGetSchema() {
        $this->assertTrue(JORK::schema('Model_User') instanceof JORK_Schema);
    }

    public function testJoinFactory() {
        $query = JORK::from('Model_User user')
            ->join('posts.topics')
            ->join('posts.topics.creator topic_creator');

        $this->assertEquals(count($query->joins), 1);

        $this->assertTrue($query->joins[0] instanceof JORK_Query_Join);

        $this->assertEquals($query->joins[0]->entity_class, 'Model_Post');
        $this->assertNull($query->joins[0]->entity_alias);

        $this->assertEquals($query->joins[0]->table, 't_posts');

        $this->assertNull($query->joins[0]->table_alias);


        $this->assertEquals(count($query->joins[0]->joins), 2);
        
    }

}