<?php

/**
 * Test class of module validation.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.com>
 */
class Core_Cli_ValidationTest extends Kohana_Unittest_TestCase {

    /**
     * @expectedException Cyclone_Cli_Validation_Exception
     * @expectedExceptionCode 101
     */
    public function testNoModuleDescription() {
        $arr = array();
        $mod = new Cyclone_Cli_Module("no_values", $arr);
        $mod->validate();
    }

    /**
     * @expectedException Cyclone_Cli_Validation_Exception
     * @expectedExceptionCode 102
     */
    public function testNoCommandsArray() {
        $arr = array(
            'description' => 'The desc of application
            in multi line
            hahaha'
        );
        $mod = new Cyclone_Cli_Module("no_values", $arr);
        $mod->validate();
    }

    /**
     * @expectedException Cyclone_Cli_Validation_Exception
     * @expectedExceptionCode 102
     */
    public function testEmptyCommandsArray() {
        $arr = array(
            'description' => 'The desc of application
            in multi line
            hahaha',
            'commands' => array()
        );
        $mod = new Cyclone_Cli_Module("no_values", $arr);
        $mod->validate();
    }

    /**
     * @expectedException Cyclone_Cli_Validation_Exception
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
        $mod = new Cyclone_Cli_Module("no_values", $arr);
        $mod->validate();
    }

    /**
     * @expectedException Cyclone_Cli_Validation_Exception
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
        $mod = new Cyclone_Cli_Module("no_values", $arr);
        $mod->validate();
    }

    /**
     * @expectedException Cyclone_Cli_Validation_Exception
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
        $mod = new Cyclone_Cli_Module("no_values", $arr);
        $mod->validate();
    }

    /**
     * @expectedException Cyclone_Cli_Validation_Exception
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
                        '--module' => array(
                            'alias' => 'al',
                            'parameter' => '<module name>',
                            'descr' => 'Database schema will be generated for classes in module <module name>',
                            'required' => false
                        )
                    ),
                    'callback' => array('SimpleDB_Schema_Generator', 'generate_schema')
                )
            )
        );
        $mod = new Cyclone_Cli_Module("no_values", $arr);
        $mod->validate();
    }

    /**
     * @expectedException Cyclone_Cli_Validation_Exception
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
                        '--module' => array(
                            'alias' => '-a',
                            //'parameter' => '<module name>',
                            'descr' => 'Database schema will be generated for classes in module <module name>',
                            'required' => false
                        )
                    ),
                    'callback' => array('SimpleDB_Schema_Generator', 'generate_schema')
                )
            )
        );
        $mod = new Cyclone_Cli_Module("no_values", $arr);
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
                        '--module' => array(
                            'alias' => '-a',
                            'parameter' => NULL,
                            'descr' => 'Database schema will be generated for classes in module <module name>'
                        )
                    ),
                    'callback' => array('SimpleDB_Schema_Generator', 'generate_schema')
                )
            )
        );
        $mod = new Cyclone_Cli_Module("no_values", $arr);
        $mod->validate();
    }

    /**
     * @expectedException Cyclone_Cli_Validation_Exception
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
                        '--module' => array(
                            'alias' => '-a',
                            'parameter' => 12,
                            'descr' => 'Database schema will be generated for classes in module <module name>',
                            'required' => false
                        )
                    ),
                    'callback' => array('SimpleDB_Schema_Generator', 'generate_schema')
                )
            )
        );
        $mod = new Cyclone_Cli_Module("no_values", $arr);
        $mod->validate();
    }

    /**
     * @expectedException Cyclone_Cli_Validation_Exception
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
                        '--module' => array(
                            'alias' => '-a',
                            'parameter' => 'ok param',
                            'descr' => 'Database schema will be generated for classes in module <module name>',
                            'required' => 'ok'
                        )
                    ),
                    'callback' => array('SimpleDB_Schema_Generator', 'generate_schema')
                )
            )
        );
        $mod = new Cyclone_Cli_Module("no_values", $arr);
        $mod->validate();
    }

    /**
     * @expectedException Cyclone_Cli_Validation_Exception
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
                        '--module' => array(
                            'alias' => '-a',
                            'parameter' => null,
                            'descr' => 'Database schema will be generated for classes in module <module name>',
                            'required' => TRUE
                        )
                    ),
                    'callback' => array('SimpleDB_Schema_Generator', 'generate_schema')
                )
            )
        );
        $mod = new Cyclone_Cli_Module("no_values", $arr);
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
                        '--module' => array(
                            'alias' => '-a',
                            'parameter' => null
                        )
                    ),
                    'callback' => array('SimpleDB_Schema_Generator', 'generate_schema')
                )
            )
        );
        $mod = new Cyclone_Cli_Module("no_values", $arr);
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
                        '--module' => array(
                            'alias' => '-a',
                            'parameter' => 'szoveg',
                            'descr' => 'Database schema will be generated for classes in module <module name>',
                            'required' => TRUE
                        )
                    ),
                    'callback' => array('SimpleDB_Schema_Generator', 'generate_schema')
                )
            )
        );
        $mod = new Cyclone_Cli_Module("no_values", $arr);
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
                        '--module' => array(
                            'alias' => '-m',
                            'parameter' => '<module name>',
                            'descr' => 'Database schema will be generated for classes in module <module name>',
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
        $mod = new Cyclone_Cli_Module("no_values", $arr);
        $mod->validate();
    }

}

?>
