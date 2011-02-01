<?php


class JORK_Model_CollectionTest extends Kohana_Unittest_TestCase {


    /**
     * @expectedException JORk_Exception
     */
    public function testForComponent() {
        $user = new Model_User;
        $coll = JORK_Model_Collection::for_component($user, 'posts');
        $this->assertInstanceOf('JORK_Model_Collection_OneToMany', $coll);

        $topic = new Model_Topic;
        $coll = JORK_Model_Collection::for_component($topic, 'categories');
        $this->assertInstanceOf('JORK_Model_Collection_ManyToMany', $coll);

        $coll = JORK_Model_Collection::for_component($topic, 'posts');
        $this->assertInstanceOf('JORK_Model_Collection_Reverse_ManyToOne', $coll);

        $cat = new Model_Category;
        $coll = JORK_Model_Collection::for_component($cat, 'topics');
        $this->assertInstanceOf('JORK_Model_Collection_Reverse_ManyToMany', $coll);

        $coll = JORK_Model_Collection::for_component($cat, 'moderator');
    }

}