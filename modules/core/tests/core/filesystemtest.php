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

    public function testAutloder_kohana(){
        $test_classname = 'Filesystem';
        $this->assertEquals(TRUE, FileSystem::autoloader_kohana($test_classname),'>>>>');
        $this->assertTrue(class_exists($test_classname));

        $test_classname = 'Controller_Core';
        $this->assertEquals(TRUE, FileSystem::autoloader_kohana($test_classname));
        $this->assertTrue(class_exists($test_classname));

        $test_wrong_classname = 'Its_Coudnt_Exist';
        $this->assertFalse(FileSystem::autoloader_kohana($test_wrong_classname));
        $this->assertFalse(class_exists($test_wrong_classname));
    }
}
?>
