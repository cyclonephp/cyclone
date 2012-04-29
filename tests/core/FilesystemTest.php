<?php

use cyclone as cy;

class Core_FileSystemTest extends Kohana_Unittest_TestCase {

    public function testFind_file(){
        $test_abs_path = cy\FileSystem::find_file('classes/cyclone/FileSystem.php');
        $this->assertEquals($test_abs_path, SYSPATH . 'classes/cyclone/FileSystem.php');

        $test_abs_path = cy\FileSystem::find_file('classes/cyclone/File.php');
        $this->assertEquals($test_abs_path, SYSPATH.'classes/cyclone/File.php');
    }

    public function testAutoloader_camelcase(){
        
    }
}
