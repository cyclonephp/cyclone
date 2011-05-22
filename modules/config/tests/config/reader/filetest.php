<?php


class Config_Reader_FileTest extends Kohana_Unittest_TestCase {

    public $reader;

    public function  setUp() {
        $this->reader = new Config_Reader_File;
    }

    public function testGet() {
        $val = $this->reader->read('sample.hello.world');
        $this->assertEquals('default', $val);
        
    }
    
}