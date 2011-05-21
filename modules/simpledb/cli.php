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
                        'descr' => 'Database schema will be generated for classes in module <module name>',
                        'required' => false
                    ),
                    '--forced' => array(
                        'alias' => '-f',
                        'parameter' => NULL,
                        'descr' => 'Tables will be dropped before creation'
                    )
                ),
                'callback' => array('SimpleDB_Schema_Generator', 'generate_schema')
            )
        )
    )
);

?>
