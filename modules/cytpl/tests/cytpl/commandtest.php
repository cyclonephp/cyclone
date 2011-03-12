<?php

class CyTpl_CommandTest extends Kohana_Unittest_TestCase {

    /**
     * @dataProvider providerValidate
     */
    public function testValidate($name, $descr, $args, $should_fail) {
        $failed = FALSE;
        try {
            new CyTpl_Command($name, $descr, $args);
        } catch (CyTpl_Command_Exception $ex) {
            $failed = TRUE;
        }
        $this->assertEquals($should_fail, $failed);
    }

    public function providerValidate() {
        return array(
            array('c', array(), array(), TRUE),
            array('c', array('callback' => 0), array(), TRUE),
            array('c', array('callback' => 0, 'params' => array(0)), array(), TRUE),
            array('c', array('callback' => 0, 'params' => array(0, 'asd'))
                , array(1, 'asd' => 2), FALSE)
        );
    }
    
}