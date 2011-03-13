<?php

/**
 * This test class is a general test class used for general functional testing
 */
class JORK_Model_Playground extends JORK_DbTest {

    public function testIfJORKCanSaveTheWorld() {
        $user = new Model_User;
        $user->name = 'newbie01';

        $topic = new Model_Topic;
        $topic->name = 'newbie question - PLEASE HELP';

        for ($i = 0; $i < 6; ++$i) {
            $post = new Model_Post;
            $post->name = "newbie post $i";

            $post->topic = $topic;

            //$post->topic = $topic;
            $user->posts->append($post);
        }

        echo "\n" . $user . "\n";

        //$user->save();
    }

}