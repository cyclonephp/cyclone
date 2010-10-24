<?php


class Config_Reader_EnvTest extends Kohana_Unittest_TestCase {

    
    public function  tearDown() {
        Config::inst()->readers = array();
    }

    public function testDefault() {
        Kohana::$environment = Kohana::DEVELOPMENT;
        Config::inst()->readers = array(new Config_Reader_File_Env);
        $this->assertEquals(Config::inst()->get('sample.hello.world'), 'default');
        $this->assertEquals(Config::inst()->get('sample.hello.onlydefault'), 'defval');
    }

    /**
     * @expectedException Config_Exception
     */
    public function testEnv() {
        Kohana::$environment = Kohana::TESTING;
        Config::inst()->readers = array(new Config_Reader_File_Env);
        $this->assertEquals(Config::inst()->get('sample.hello.world'), 'test');
        $this->assertEquals(Config::inst()->get('sample.hello.onlytest'), 'testval');
        Config::inst()->get('asd');
    }
}