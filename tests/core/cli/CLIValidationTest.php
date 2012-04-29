<?php

use cyclone\cli;

/**
 * Test class of library validation.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.org>
 */
class Core_Cli_ValidationTest extends Kohana_Unittest_TestCase {

    /**
     * @expectedException \cyclone\cli\ValidationException
     * @expectedExceptionCode 101
     */
    public function testNolibraryDescription() {
        $arr = array();
        $mod = new cli\Library("no_values", $arr);
        $mod->validate();
    }

    /**
     * @expectedException \cyclone\cli\ValidationException
     * @expectedExceptionCode 102
     */
    public function testNoCommandsArray() {
        $arr = array(
            'description' => 'The desc of application
            in multi line
            hahaha'
        );
        $mod = new cli\Library("no_values", $arr);
        $mod->validate();
    }

    /**
     * @expectedException \cyclone\cli\ValidationException
     * @expectedExceptionCode 102
     */
    public function testEmptyCommandsArray() {
        $arr = array(
            'description' => 'The desc of application
            in multi line
            hahaha',
            'commands' => array()
        );
        $mod = new cli\Library("no_values", $arr);
        $mod->validate();
    }

    /**
     * @expectedException \cyclone\cli\ValidationException
     * @expectedExceptionCode 103
     */
    public function testNoCommandDesc() {
        $arr = array(
            'description' => 'The desc of application
            in multi line
            hahaha',
            'commands' => array(
                'generate-schema' => array()
            )
        );
        $mod = new cli\Library("no_values", $arr);
        $mod->validate();
    }

    /**
     * @expectedException \cyclone\cli\ValidationException
     * @expectedExceptionCode 103
     */
    public function testNoCommandDescEmptyString() {
        $arr = array(
            'description' => 'The desc of application
            in multi line
            hahaha',
            'commands' => array(
                'generate-schema' => array('desc' => '')
            )
        );
        $mod = new cli\Library("no_values", $arr);
        $mod->validate();
    }

    /**
     * @expectedException \cyclone\cli\ValidationException
     * @expectedExceptionCode 104
     */
    public function testNoCommandCallback() {
        $arr = array(
            'description' => 'The desc of application
            in multi line
            hahaha',
            'commands' => array('generate-schema' => array(
                    'descr' => 'description asdasdasd',
            ))
        );
        $mod = new cli\Library("no_values", $arr);
        $mod->validate();
    }

    /**
     * @expectedException \cyclone\cli\ValidationException
     * @expectedExceptionCode 105
     */
    public function testbadArgumentAlias() {
        $arr = array(
            'description' => 'The desc of application',
            'commands' => array(
                'generate-schema' => array(
                    'description' => "Generates database schema.
Iterates on all classes named Record_*, instantiates each one and creates database schema for them.",
                    'arguments' => array(
                        '--library' => array(
                            'alias' => 'al',
                            'parameter' => '<library name>',
                            'descr' => 'Database schema will be generated for classes in library <library name>',
                            'required' => false
                        )
                    ),
                    'callback' => array('SimpleDB_Schema_Generator', 'generate_schema')
                )
            )
        );
        $mod = new cli\Library("no_values", $arr);
        $mod->validate();
    }

    /**
     * @expectedException \cyclone\cli\ValidationException
     * @expectedExceptionCode 106
     */
    public function testArgParamNotDef() {
        $arr = array(
            'description' => 'The desc of application',
            'commands' => array(
                'generate-schema' => array(
                    'description' => "Generates database schema.
Iterates on all classes named Record_*, instantiates each one and creates database schema for them.",
                    'arguments' => array(
                        '--library' => array(
                            'alias' => '-a',
                            //'parameter' => '<library name>',
                            'descr' => 'Database schema will be generated for classes in library <library name>',
                            'required' => false
                        )
                    ),
                    'callback' => array('SimpleDB_Schema_Generator', 'generate_schema')
                )
            )
        );
        $mod = new cli\Library("no_values", $arr);
        $mod->validate();
    }

    public function testArgParamCanDefNull() {
        $arr = array(
            'description' => 'The desc of application',
            'commands' => array(
                'generate-schema' => array(
                    'description' => "Generates database schema.
Iterates on all classes named Record_*, instantiates each one and creates database schema for them.",
                    'arguments' => array(
                        '--library' => array(
                            'alias' => '-a',
                            'parameter' => NULL,
                            'descr' => 'Database schema will be generated for classes in library <library name>'
                        )
                    ),
                    'callback' => array('DB_Schema_Generator', 'generate_schema')
                )
            )
        );
        $mod = new cli\Library("no_values", $arr);
        $mod->validate();
    }

    /**
     * @expectedException \cyclone\cli\ValidationException
     * @expectedExceptionCode 107
     */
    public function testArgParamBadDefNumb() {
        $arr = array(
            'description' => 'The desc of application',
            'commands' => array(
                'generate-schema' => array(
                    'description' => "Generates database schema.
Iterates on all classes named Record_*, instantiates each one and creates database schema for them.",
                    'arguments' => array(
                        '--library' => array(
                            'alias' => '-a',
                            'parameter' => 12,
                            'descr' => 'Database schema will be generated for classes in library <library name>',
                            'required' => false
                        )
                    ),
                    'callback' => array('SimpleDB_Schema_Generator', 'generate_schema')
                )
            )
        );
        $mod = new cli\Library("no_values", $arr);
        $mod->validate();
    }

    /**
     * @expectedException \cyclone\cli\ValidationException
     * @expectedExceptionCode 108
     */
    public function testArgRequiredBadType() {
        $arr = array(
            'description' => 'The desc of application',
            'commands' => array(
                'generate-schema' => array(
                    'description' => "Generates database schema.
Iterates on all classes named Record_*, instantiates each one and creates database schema for them.",
                    'arguments' => array(
                        '--library' => array(
                            'alias' => '-a',
                            'parameter' => 'ok param',
                            'descr' => 'Database schema will be generated for classes in library <library name>',
                            'required' => 'ok'
                        )
                    ),
                    'callback' => array('SimpleDB_Schema_Generator', 'generate_schema')
                )
            )
        );
        $mod = new cli\Library("no_values", $arr);
        $mod->validate();
    }

    /**
     * @expectedException \cyclone\cli\ValidationException
     * @expectedExceptionCode 109
     */
    public function testArgRequiredNoSense() {
        $arr = array(
            'description' => 'The desc of application',
            'commands' => array(
                'generate-schema' => array(
                    'description' => "Generates database schema.
Iterates on all classes named Record_*, instantiates each one and creates database schema for them.",
                    'arguments' => array(
                        '--library' => array(
                            'alias' => '-a',
                            'parameter' => null,
                            'descr' => 'Database schema will be generated for classes in library <library name>',
                            'required' => TRUE
                        )
                    ),
                    'callback' => array('SimpleDB_Schema_Generator', 'generate_schema')
                )
            )
        );
        $mod = new cli\Library("no_values", $arr);
        $mod->validate();
    }

    public function testArgRequiredMissing() {
        $arr = array(
            'description' => 'The desc of application',
            'commands' => array(
                'generate-schema' => array(
                    'description' => "Generates database schema.
Iterates on all classes named Record_*, instantiates each one and creates database schema for them.",
                    'arguments' => array(
                        '--library' => array(
                            'alias' => '-a',
                            'parameter' => null
                        )
                    ),
                    'callback' => array('SimpleDB_Schema_Generator', 'generate_schema')
                )
            )
        );
        $mod = new cli\Library("no_values", $arr);
        $mod->validate();
    }

    public function testArgParamGoodDef() {
        $arr = array(
            'description' => 'The desc of application',
            'commands' => array(
                'generate-schema' => array(
                    'description' => "Generates database schema.
Iterates on all classes named Record_*, instantiates each one and creates database schema for them.",
                    'arguments' => array(
                        '--library' => array(
                            'alias' => '-a',
                            'parameter' => 'szoveg',
                            'descr' => 'Database schema will be generated for classes in library <library name>',
                            'required' => TRUE
                        )
                    ),
                    'callback' => array('SimpleDB_Schema_Generator', 'generate_schema')
                )
            )
        );
        $mod = new cli\Library("no_values", $arr);
        $mod->validate();
    }

    public function testFullGood() {
        $arr = array(
            'description' => 'The desc of application\n
                              in multi line\n
                              hahaha',
            'commands' => array(
                'generate-schema' => array(
                    'description' => "Generates database schema.

Iterates on all classes named Record_*, instantiates each one and creates database schema for them.",
                    'arguments' => array(
                        '--library' => array(
                            'alias' => '-m',
                            'parameter' => '<library name>',
                            'descr' => 'Database schema will be generated for classes in library <library name>',
                            'required' => TRUE
                        ),
                        '--forced' => array(
                            'parameter' => NULL,
                            'descr' => 'Tables will be dropped before creation'
                        ),
                        '--help' => array(
                            'parameter' => NULL,
                            'descr' => 'help help help',
                            'required' => FALSE
                        )
                    ),
                    'callback' => array('SimpleDB_Schema_Generator', 'generate_schema')
                )
            )
        );
        $mod = new cli\Library("no_values", $arr);
        $mod->validate();
    }

}

?>
