<?php

use cyclone\FileSystem;

class Core_FileSystemTest extends Kohana_Unittest_TestCase {

    private static $_source_file = '/tmp/hello.txt';

    private static $_target_file = '/tmp/world.txt';

    private static $_target_file_2 = '/tmp/hel/lo.txt';

    private static $_nonexistent_file = '/tmp/non.existent';

    public function setUp() {
        parent::setUp();
        if (is_dir('/tmp')) {
            file_exists(self::$_source_file) && unlink(self::$_source_file);
            file_exists(self::$_target_file) && unlink(self::$_target_file);
            file_exists(self::$_target_file_2) && unlink(self::$_target_file_2);
            is_dir('/tmp/hel') && rmdir('/tmp/hel');
        }
    }

    public function test_find_file(){
        $test_abs_path = FileSystem::get_default()->find_file('classes/cyclone/FileSystem.php');
        $this->assertEquals($test_abs_path, SYSPATH . 'classes/cyclone/FileSystem.php');

        $test_abs_path = FileSystem::get_default()->find_file('classes/cyclone/File.php');
        $this->assertEquals($test_abs_path, SYSPATH.'classes/cyclone/File.php');
    }

    public function test_autoloader_camelcase(){
        
    }

    /**
     * @expectedException \cyclone\FileSystemException
     * @expectedExceptionCode 1
     */
    public function test_nonexistent_source_copy() {
        if (file_exists(self::$_nonexistent_file)) {
            $this->markTestSkipped('file ' . self::$_nonexistent_file . ' exists');
        }
        FileSystem::get_default()->copy(self::$_nonexistent_file, self::$_target_file);
    }

    public function test_copy() {
        if ( ! is_dir('/tmp')) {
            $this->markTestSkipped("/tmp does not exist");
            return;
        }
        file_put_contents(self::$_source_file, 'hello');
        FileSystem::get_default()->copy(self::$_source_file, self::$_target_file);
        $this->assertTrue(file_exists(self::$_target_file));
        $this->assertEquals('hello', file_get_contents(self::$_target_file));

        FileSystem::get_default()->copy(self::$_source_file, self::$_target_file_2);
        $this->assertTrue(file_exists(self::$_target_file_2));
        $this->assertEquals('hello', file_get_contents(self::$_target_file_2));
    }
}
