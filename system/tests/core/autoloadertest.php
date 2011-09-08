<?php

class Core_AutoLoaderTest {

    public function testAutloder_Kohana(){
        $test_classname = 'FileSystem';
        $this->assertEquals(TRUE, Autoloader_Kohana::inst()->autoload($test_classname));
        $this->assertTrue(class_exists($test_classname));

        $test_classname = 'Controller_Core';
        $this->assertEquals(TRUE, Autoloader_Kohana::inst()->autoload($test_classname));
        $this->assertTrue(class_exists($test_classname));

        $test_wrong_classname = 'Its_Coudnt_Exist';
        $this->assertFalse(Autoloader_Kohana::inst()->autoload($test_wrong_classname));
        $this->assertFalse(class_exists($test_wrong_classname));
    }

    
}