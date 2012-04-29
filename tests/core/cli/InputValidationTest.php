<?php

use cyclone as cy;
use cyclone\cli;

class InputValidationTest extends Kohana_Unittest_TestCase {

    public function testCompactAliases() {
        $expected = array(
            '--forced' => TRUE,
            '--help' => FALSE,
            '--library' => 'libname'
        );
        $actual = array();
        $lib = cli\LibraryHandler::inst()->set_libs(array('mylib' =>
        array(
            'description' => ' ',
            'commands' => array(
                'mycommand' => array(
                    'description' => " ",
                    'arguments' => array(
                        '--library' => array(
                            'alias' => '-l',
                            'parameter' => '<library name>',
                            'descr' => 'Database schema will be generated for classes in library <library name>',
                            'required' => TRUE
                        ),
                        '--forced' => array(
                            'parameter' => NULL,
                            'descr' => 'Tables will be dropped before creation',
                            'alias' => '-f'
                        ),
                        '--help' => array(
                            'parameter' => NULL,
                            'descr' => 'help help help',
                            'required' => FALSE
                        )
                    ),
                    'callback' => function($params) use (&$actual) {
                        $actual = $params;
                    }
                )
            )
        )
        ))->get_library('mylib');
        $validator = new cli\InputValidator(explode(' ', 'cyphp mylib mycommand -fl libname'), $lib);
        $validator->validate();
        $this->assertEquals($expected, $actual);
    }
    
}