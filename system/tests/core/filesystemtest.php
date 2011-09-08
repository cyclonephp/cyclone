<?php

use cyclone as cy;

class Core_FileSystemTest extends Kohana_Unittest_TestCase {

    public function testFind_file(){
        $test_abs_path = cy\FileSystem::find_file('classes/cyclone/filesystem.php');
        $this->assertEquals($test_abs_path, SYSPATH . 'classes/cyclone/filesystem.php');

        $test_abs_path = cy\FileSystem::find_file('classes/cyclone/file.php');
        $this->assertEquals($test_abs_path, SYSPATH.'classes/cyclone/file.php');
    }

    public function testAutoloader_camelcase(){
        
    }
}
