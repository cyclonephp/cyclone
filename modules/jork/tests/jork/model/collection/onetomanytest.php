<?php


class JORK_Model_Collection_OneToManyTest extends Kohana_Unittest_TestCase {

    public function testAppend() {
        $user = new Model_User;
        $user->id = 15;
        $post = new Model_Post;
        $user->posts->append($post);
        $this->assertEquals(15, $post->user_fk);
        $this->assertEquals(1, count($user->posts));
        //$this->assertEquals($user, $post->author);
    }

    public function testDelete() {
        $user = new Model_User;
        $user->id = 15;
        $post = new Model_Post;
        $post->id = 12;
        $user->posts->append($post);

        unset($user->posts[12]);
        $this->assertEquals(0, count($user->posts));
    }

    public function testSave() {
        $user = new Model_User;
        $user->name = 'foo bar';
        $post = new Model_Post;
        $user->posts->append($post);
        $user->save();
        $this->assertEquals(5, $user->id);
        $this->assertEquals(5, $post->id);
        $this->assertEquals(5, $post->user_fk);

        $result = DB::select()->from('t_posts')->where('id', '=', DB::esc(5))->exec('jork_test');
        $this->assertEquals(1, count($result));

        $user->posts->delete($post);
        $user->posts->save();

        $result = DB::select()->from('t_posts')->where('id', '=', DB::esc(5))->exec('jork_test');
        //$this->markTestSkipped('missing JORK_Model_Abstract::update() implementation');
        foreach ($result as $row) {
            $this->assertEquals(0, $row['user_fk']);
        }
        
    }
}
