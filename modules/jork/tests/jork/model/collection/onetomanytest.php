<?php


class JORK_Model_Collection_OneToManyTest extends Kohana_Unittest_TestCase {

    public function testAppend() {
        $user = new Model_User;
        $user->id = 15;
        $post = new Model_Post;
        $user->posts->append($post);
        $this->assertEquals(15, $post->user_fk);
        $this->assertEquals(1, count($user->posts));
    }

    public function testUnset() {
        $user = new Model_User;
        $user->id = 15;
        $post = new Model_Post;
        $post->id = 12;
        $user->posts->append($post);

        $user->posts->delete($post);
        $this->assertEquals(0, count($user->posts));
    }
}
