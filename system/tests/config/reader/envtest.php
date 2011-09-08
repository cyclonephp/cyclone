<?php

use cyclone as cy;

class Config_Reader_EnvTest extends Kohana_Unittest_TestCase {

    
    public function  tearDown() {
        cy\Config::inst()->readers = array();
        parent::tearDown();
    }

    public function testDefault() {
        cy\Env::$current = cy\Env::DEV;
        cy\Config::inst()->readers = array(new cy\config\reader\FileEnv);
        $this->assertEquals(cy\Config::inst()->get('sample.hello.world'), 'default');
        $this->assertEquals(cy\Config::inst()->get('sample.hello.onlydefault'), 'defval');
    }

    /**
     * @expectedException Config_Exception
     */
    public function testEnv() {
        cy\Env::$current = cy\Env::TEST;
        cy\Config::inst()->readers = array(new cy\config\reader\FileEnv);
        $this->assertEquals(cy\Config::inst()->get('sample.hello.world'), 'test');
        $this->assertEquals(cy\Config::inst()->get('sample.hello.onlytest'), 'testval');
        cy\Config::inst()->get('asd');
    }
}