<?php


class JORK_Alias_FactoryTest extends Kohana_Unittest_TestCase {

    public function testForTable() {
        $alias_factory = new JORK_Alias_Factory();

        $this->assertEquals($alias_factory->for_table('users'), 'users_1');
        $this->assertEquals($alias_factory->for_table('users'), 'users_2');

        $this->assertEquals($alias_factory->for_table('posts'), 'posts_1');
    }
}