<?php

return array(
    'simpledb' => array(
        'description' => 'SimpleDB is a low-level database abstraction layer for CyclonePHP',
        'commands' => array(
            'generate-schema' => array(
                'description' => "Generates database schema.

Iterates on all classes named Record_*, instantiates each one and creates database schema for them.",
                'arguments' => array(
                    '--module' => array(
                        'alias' => '-m',
                        'parameter' => '<module-name>',
                        'descr' => 'Database schema will be generated for classes in module <module name>.
                            You can pass multiple modules by passing a comma-separated list of module names (eg. -m frontend,backend)',
                        'required' => false
                    ),
                    '--forced' => array(
                        'alias' => '-f',
                        'parameter' => NULL,
                        'descr' => 'Tables will be dropped before creation'
                    ),
                    '--suppress-execution' => array(
                        'alias' => '-s',
                        'parameter' => NULL,
                        'descr' => 'Prints the generated DDL to stdout and does not execute it'
                    )
                ),
                'callback' => array('DB_Schema_Builder', 'build_schema')
            )
        )
    )
);

?>
