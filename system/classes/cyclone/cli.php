<?php

namespace cyclone;

/**
 * Main class of Cyclone CLI.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.com>
 * @usedby cyphp
 * @package cyclone
 */
class CLI {

    const INTRO = "For library help type: cyphp <library_name> .\nAvailable libraries:";

    /**
     * Returns one or more command-line options. Options are specified using
     * standard CLI syntax:
     *
     *     php index.php --username "john.smith" --password "secret"
     *
     *     // Get the values of "username" and "password"
     *     $auth = CLI::options('username', 'password');
     *
     * This method has been copied from Kohana 3.0.
     *
     * @param   string  option name
     * @param   ...
     * @return  array
     */
    public static function options($options) {
        // Get all of the requested options
        $options = func_get_args();

        // Found option values
        $values = array();

        // Skip the first option, it is always the file executed
        for ($i = 1; $i < $_SERVER['argc']; $i++) {
            if (!isset($_SERVER['argv'][$i])) {
                // No more args left
                break;
            }

            // Get the option
            $opt = $_SERVER['argv'][$i];

            if (substr($opt, 0, 2) !== '--') {
                // This is not an option argument
                continue;
            }

            // Remove the "--" prefix
            $opt = substr($opt, 2);

            if (strpos($opt, '=')) {
                // Separate the name and value
                list ($opt, $value) = explode('=', $opt, 2);
            } else {
                $value = NULL;
            }

            if (in_array($opt, $options)) {
                // Set the given value
                $values[$opt] = $value;
            }
        }

        return $values;
    }

    public static function bootstrap() {

        /** function has not called from cyphp */
        if ($_SERVER['argv'][0] != 'cyphp' && $_SERVER['argv'][0] != './cyphp') {
            echo Cyclone_Cli_Errors::CALL_ERROR . PHP_EOL;
            return;
        }

        $library_handler = Cyclone_Cli_library_Handler::inst();
        $param_num = count($_SERVER['argv']);

        if ($param_num == 1 || $library_handler->is_exists($_SERVER['argv'][1]) === false) {
            echo self::INTRO . PHP_EOL;
            $library_handler->show_short_help();
        } else {
            $library = $library_handler->get_library($_SERVER['argv'][1]);
            try {
                $library->validate();
            } catch (Cyclone_Cli_Validation_Exception $ex) {
                echo $ex->getMessage();
                return;
            }

            $input_validator = new Cyclone_Cli_Input_Validator($_SERVER['argv'], $library);
            $input_validator->validate();
        }
    }

}
