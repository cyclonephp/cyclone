<?php

class Core_FileSystemTest extends Kohana_Unittest_TestCase {

    public function testFind_file(){
        $test_abs_path = FileSystem::find_file('classes'.DIRECTORY_SEPARATOR.'filesystem.php');
        $this->assertEquals($test_abs_path, SYSPATH . 'classes'.DIRECTORY_SEPARATOR.'filesystem.php');

        $test_abs_path = FileSystem::find_file('classes'.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR.'app.php');
        $this->assertEquals($test_abs_path, APPPATH.'classes'.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR.'app.php');

        $test_abs_path = FileSystem::find_file('classes'.DIRECTORY_SEPARATOR.'file.php');
        $this->assertEquals($test_abs_path, SYSPATH.'classes'.DIRECTORY_SEPARATOR.'file.php');
    }

    public function testAutoloader_camelcase(){
        
    }
}
