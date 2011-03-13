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

        $run = 0;
        for ($i = 0; $i < 6; ++$i) { ++$run;
            $post = new Model_Post;
            $post->name = "newbie post $i";
            //$post->topic = $topic;
            $user->posts->append($post);
        }
        
        $user->save();
    }

}