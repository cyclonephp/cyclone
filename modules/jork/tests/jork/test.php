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

    public function testJoin() {
        $query = JORK::from('Model_User user')
            ->join('posts.topics user_topics')
            ->join('posts.topics.creator topic_creator');
        $expected = new ArrayObject(array(
                    array(
                        'component' => 'posts',
                        'nexts' => new ArrayObject(array(
                            array(
                                'component' => 'topics',
                                'nexts' => new ArrayObject(array(
                                    array(
                                        'component' => 'creator',
                                        'nexts' => new ArrayObject,
                                        'alias' => 'topic_creator',
                                    )
                                )),
                                'alias' => 'user_topics',
                            ))
                        )
                    )
                ));


        $this->assertEquals($query->joins, $expected);
        
    }

}