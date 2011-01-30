<?php


class JORK_Model_Collection_ManyToManyTest extends Kohana_Unittest_TestCase {

    public function testAppend() {
        $topic = new Model_Topic;
        $category = new Model_Category;
        $category->id = 3;
        $topic->categories->append($category);
        $this->assertEquals(1, count($topic->categories));
        $this->assertEquals($topic->categories[3], $category);
    }

    public function testDelete() {
        $topic = new Model_Topic;
        $category = new Model_Category;
        $category->id = 3;
        $topic->categories->append($category);
        $this->assertEquals(1, count($topic->categories));

        $topic->categories->delete($category);
        $this->assertEquals(0, count($topic->categories));
    }
}