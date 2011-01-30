<?php


class JORK_Model_Test extends Kohana_Unittest_TestCase {

    public function testInst() {
        Model_User::inst();
    }

    public function testManyToOneFK() {
        $post = new Model_Post;
        $topic = new Model_Topic;
        $topic->id = 10;
        $post->topic = $topic;
        $this->assertEquals(10, $post->topic_fk);
    }

    public function testManyToOneReverseFK() {
        $post = new Model_Post;
        $user = new Model_User;
        $user->id = 6;
        $post->author = $user;
        $this->assertEquals($post->user_fk, 6);
    }

    public function testOneToOneFK() {
        $category = new Model_Category;
        $user = new Model_User;
        $user->id = 5;
        $category->moderator = $user;
        $this->assertEquals(5, $category->moderator_fk);
    }

    public function testOneToOneReverseFK() {
        $category = new Model_Category;
        $user = new Model_User;
        $user->id = 3;
        $user->moderated_category = $category;
        $this->assertEquals($category->moderator_fk, 3);
    }

    public function testOneToManyFK() {
        $user = new Model_User;
        $user->id = 34;
        $post = new Model_Post;
        //$this->markTestSkipped('not yet implemented');
        $user->posts->append($post);
        $this->assertEquals($post->user_fk, 34);
    }

    public function testOneToManyReverseFK() {
        $topic = new Model_Topic;
        $topic->id = 2;
        $post = new Model_Post;
        //$this->markTestSkipped('not yet implemented');
        $topic->posts->append($post);
        $this->assertEquals(2, $post->topic_fk);
    }

    public function testPk() {
        $user = new Model_User();
        $user->id = 5;
        $this->assertEquals(5, $user->pk());
    }
}