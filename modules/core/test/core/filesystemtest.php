<?php

class Core_FileSystemTest extends Kohana_Unittest_TestCase {

    public function testFind_file(){
        $test_abs_path = FileSystem::find_file('classes/filesystem.php');
        $this->assertEquals($test_abs_path, MODPATH.'core/classes/filesystem.php');

        $test_abs_path = FileSystem::find_file('classes/controller/app.php');
        $this->assertEquals($test_abs_path, APPPATH.'classes/controller/app.php');

        $test_abs_path = FileSystem::find_file('classes/file.php');
        $this->assertEquals($test_abs_path, SYSPATH.'classes/file.php');
    }
}
?>
