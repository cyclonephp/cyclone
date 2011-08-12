<?php

/**
 * Main class of Cyclone CLI.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.com>
 * @usedby index.php
 */
class Cyclone_CLI {
    const INTRO = "For library help type: cyphp <library_name> .\nAvailable libraries:";

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
