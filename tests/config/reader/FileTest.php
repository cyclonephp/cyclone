<?php

use cyclone as cy;

class Config_Reader_FileTest extends Kohana_Unittest_TestCase {

    public $reader;

    public function  setUp() {
        parent::setUp();
        $this->reader = new cy\config\reader\File;
    }

    public function testGet() {
        $val = $this->reader->read('sample.hello.world');
        $this->assertEquals('default', $val);
        
    }
    
}