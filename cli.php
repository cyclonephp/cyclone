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
            ),
            'list-examples' => array(
                'description' => 'lists the examples available in the installed libraries',
                'arguments' => array(),
                'callback' => array('cyclone\\FileSystem', 'list_examples')
            ),
            'install-example' => array(
                'description' => 'installs an example from the examples/ directory of a library to the root directory of an other library.

The destination library is recommeneded to be a local "sandbox" application library.',
                'arguments' => array(
                    '--example' => array(
                        'alias' => '-e',
                        'required' => TRUE,
                        'parameter' => '<example>',
                        'descr' => 'the name of the example to be installed. It should be a name listed by ./cyphp system list-examples'
                    ),
                    '--destination' => array(
                        'alias' => '-d',
                        'parameter' => '<destination-library>',
                        'default' => 'app',
                        'descr' => 'the name of the destination library where the example should be copied'
                    ),
                    '--forced' => array(
                        'alias' => '-f',
                        'default' => FALSE,
                        'parameter' => NULL,
                        'descr' => 'existing files in <destination-library> will be overwritten'
                    )
                ),
                'callback' => array('cyclone\\FileSystem', 'install_example')
            )
        )
    )
);

