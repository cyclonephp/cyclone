<?php

return array(
    'system' => array(
        'description' => 'Core system commands of CyclonePHP',
        'commands' => array(
            'initlib' => array(
                'description' => "Creates an initial application/library directory structure.
                    ",
                'arguments' => array(
                    '--directory' => array(
                        'alias' => '-d',
                        'parameter' => '<path>',
                        'descr' => 'The root directory of the new library',
                        'required' => true
                    ),
                ),
                'callback' => array('cyclone\\FileSystem', 'init_lib_dirs')
            )
        )
    )
);

