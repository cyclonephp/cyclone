<?php


class Config_Test extends Kohana_Unittest_TestCase {

    /**
     * @expectedException Config_Exception
     */
    public function testAttachDetach() {
        $file_reader = new Config_Reader_File;
        $db_reader = new Config_Reader_Database;
        Config::inst('test')->attach_reader($file_reader);
        Config::inst('test')->attach_reader($db_reader);
        $this->assertEquals(Config::inst('test')->readers, array($file_reader, $db_reader));
        Config::inst('test')->detach_reader($file_reader);
        Config::inst('test')->detach_reader($db_reader);
        $this->assertEquals(Config::inst('test')->readers, array());
        Config::inst('test')->detach_reader($db_reader);
    }

    public function testSetup() {
        Config::setup();
        $this->assertTrue(Config::inst()->readers[0] instanceof Config_Reader_File_Env);
    }
    
}