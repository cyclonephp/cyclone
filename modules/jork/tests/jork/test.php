<?php


class JORK_Test extends Kohana_Unittest_TestCase {

    public function testBasicSelect() {
        $query = JORK::select('Model_User');
        $this->assertTrue($query instanceof  JORK_Query_Select);
    }

    public function testSelect() {
        $query = JORK::select(array('Model_User', 'user'))
            ->join(array('user.posts', 'post'))
            ->join(array('post.topic', 'topic'))
            ->join(array('topic.categories', 'categories'))
                ;
    }

}