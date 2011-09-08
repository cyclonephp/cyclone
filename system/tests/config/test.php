<?php

use cyclone as cy;

class Config_Test extends Kohana_Unittest_TestCase {

    /**
     * @expectedException \cyclone\config\Exception
     */
    public function testAttachDetach() {
        $file_reader = new cy\config\reader\File;
        $db_reader = new cy\config\reader\Database('config', 'key', 'value');
        cy\Config::inst('test')->attach_reader($file_reader);
        cy\Config::inst('test')->attach_reader($db_reader);
        $this->assertEquals(cy\Config::inst('test')->readers, array($file_reader, $db_reader));
        cy\Config::inst('test')->detach_reader($file_reader);
        cy\Config::inst('test')->detach_reader($db_reader);
        $this->assertEquals(cy\Config::inst('test')->readers, array());
        cy\Config::inst('test')->detach_reader($db_reader);
    }

    public function testSetup() {
        cy\Config::setup();
        $this->assertTrue(cy\Config::inst()->readers[0] instanceof cy\config\reader\FileEnv);
    }

    public function testPrependMock() {
        cy\Config::inst()->prepend_mock();
        $this->assertEquals(cy\config\MockStorage::inst(), cy\Config::inst()->readers[0]);
        $this->assertEquals(cy\config\MockStorage::inst(), cy\Config::inst()->writers[0]);
    }
    
}