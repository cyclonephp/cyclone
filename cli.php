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
                        'required' => TRUE
                    ),
                    '--app' => array(
                        'alias' => '-a',
                        'parameter' => NULL,
                        'descr' => 'If TRUE then the new library will be initialized as an application. Default config and layout view will be included.'
                    )
                ),
                'callback' => array('cyclone\\FileSystem', 'init_lib_dirs')
            ),
            'package-example' => array(
                'description' => 'Packages a library under the examples/ directory of an other library

Example:
# copies the contents of the app library to lib/cyform/examples/hello-cyform/
./cyphp system package-example -s app -d cyform -n hello-cyform -f
                ',
                'arguments' => array(
                    '--src-lib' => array(
                        'alias' => '-s',
                        'parameter' => '<library-name>',
                        'descr' => 'source library which will be packaged',
                        'required' => TRUE
                    ),
                    '--dst-lib' => array(
                        'alias' => '-d',
                        'parameter' => '<library-name>',
                        'descr' => 'destination library (which will contain the packaged example)',
                        'required' => TRUE
                    ),
                    '--name' => array(
                        'alias' => '-n',
                        'parameter' => '<example-name>',
                        'descr' => 'the name of the example (will be used later to install the example)',
                        'required' => TRUE
                    ),
                    '--forced' => array(
                        'alias' => '-f',
                        'default' => FALSE,
                        'parameter' => NULL,
                        'descr' => 'force override'
                    )
                ),
                'callback' => array('cyclone\\FileSystem', 'package_example')
            )
        )
    )
);

