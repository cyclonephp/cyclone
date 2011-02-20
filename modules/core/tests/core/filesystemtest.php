<?php

class Core_FileSystemTest extends Kohana_Unittest_TestCase {

    public function testFind_file(){
        $test_abs_path = FileSystem::find_file('classes'.DIRECTORY_SEPARATOR.'filesystem.php');
        $this->assertEquals($test_abs_path, MODPATH.'core'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'filesystem.php');

        $test_abs_path = FileSystem::find_file('classes'.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR.'app.php');
        $this->assertEquals($test_abs_path, APPPATH.'classes'.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR.'app.php');

        $test_abs_path = FileSystem::find_file('classes'.DIRECTORY_SEPARATOR.'file.php');
        $this->assertEquals($test_abs_path, SYSPATH.'classes'.DIRECTORY_SEPARATOR.'file.php');
    }

    public function testAutloder_kohana(){
        $test_classname = 'FileSystem';
        $this->assertEquals(TRUE, FileSystem::autoloader_kohana($test_classname));
        $this->assertTrue(class_exists($test_classname));

        $test_classname = 'Controller_Core';
        $this->assertEquals(TRUE, FileSystem::autoloader_kohana($test_classname));
        $this->assertTrue(class_exists($test_classname));

        $test_wrong_classname = 'Its_Coudnt_Exist';
        $this->assertFalse(FileSystem::autoloader_kohana($test_wrong_classname));
        $this->assertFalse(class_exists($test_wrong_classname));
    }

    public function testAutoloader_camelcase(){
        
    }
}
?>
