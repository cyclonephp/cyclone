<?php


class JORK_Model_Collection_Reverse_ManyToOneTest extends Kohana_Unittest_TestCase {

    public function testAppend() {
        $topic = new Model_Topic;
        $topic->id = 42;
        $post = new Model_Post;
        $post->id = 23;

        $topic->posts->append($post);
        $this->assertEquals(1, count($topic->posts));
        $this->assertEquals($post, $topic->posts[23]);
        $this->assertEquals($post->topic_fk, 42);
    }

    public function testDelete() {
        $topic = new Model_Topic;
        $topic->id = 42;
        $post = new Model_Post;
        $post->id = 23;

        $topic->posts->append($post);
        $this->assertEquals(1, count($topic->posts));
        unset($topic->posts[23]);
        $this->assertEquals($post->topic_fk, NULL);
        $this->assertEquals(0, count($topic->posts));
    }
}