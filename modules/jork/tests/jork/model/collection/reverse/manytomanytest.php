<?php


class JORK_Model_Collection_Reverse_ManyToManyTest extends Kohana_Unittest_TestCase {

    public function testAppend() {
        $cat = new Model_Category;
        $topic = new Model_Topic;
        $topic->id = 4;
        $cat->topics->append($topic);

        $this->assertEquals(1, count($cat->topics));
        $this->assertEquals($topic, $cat->topics[4]);
    }

    public function testDelete() {
        $cat = new Model_Category;
        $topic = new Model_Topic;
        $topic->id = 4;
        $cat->topics->append($topic);

        unset($cat->topics[4]);
        $this->assertEquals(0, count($cat->topics));

    }
    
}