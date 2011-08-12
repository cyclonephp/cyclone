<?php

class Config_Storage_MockTest extends Kohana_Unittest_TestCase {

    public function  setUp() {
        parent::setUp();
        Config_Storage_Mock::inst()->clear();
    }

    public function testRead() {
        $inst = Config_Storage_Mock::inst();
        $inst->storage = array(
            'hello' => 'world',
            'step1' => array(
                'step2' => 'val'
            )
        );
        $this->assertEquals('world', $inst->read('hello'));
        $this->assertEquals('val', $inst->read('step1.step2'));
        $this->assertEquals(array('step2' => 'val'), $inst->read('step1'));
        $this->assertEquals(Config::NOT_FOUND, $inst->read('dummy'));
        $this->assertEquals(Config::NOT_FOUND, $inst->read('dummy.dummy2'));
    }

    public function testWrite() {
        $inst = Config_Storage_Mock::inst();
        $inst->write('step1', 'val');
        $this->assertEquals(array(
                'step1' => 'val'
            ), $inst->storage);
        $inst->clear();
        $inst->write('step1.step2', 'val');
        $this->assertEquals(array(
            'step1' => array(
                'step2' => 'val'
            )
        ), $inst->storage);
        $inst->write('step1.step3', 'val3');
        $this->assertEquals(array(
            'step1' => array(
                'step2' => 'val',
                'step3' => 'val3'
            )
        ), $inst->storage);
    }
    
}