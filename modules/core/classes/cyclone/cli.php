<?php

/**
 * Main class of Cyclone CLI.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.com>
 * @usedby index.php
 */
class Cyclone_CLI {
    const INTRO = "For module help type: cyphp <module_name> .\nAvailable modules:";

    public static function bootstrap() {

        /** function has not called from cyphp */
        if ($_SERVER['argv'][0] != 'cyphp' && $_SERVER['argv'][0] != './cyphp') {
            echo Cyclone_Cli_Errors::CALL_ERROR . PHP_EOL;
            return;
        }

        $module_handler = Cyclone_Cli_Module_Handler::inst();
        $param_num = count($_SERVER['argv']);

        if ($param_num == 1 || $module_handler->is_exists($_SERVER['argv'][1]) === false) {
            echo self::INTRO . PHP_EOL;
            $module_handler->show_short_help();
        } else {
            $module = $module_handler->get_module($_SERVER['argv'][1]);
            try {
                $module->validate();
            } catch (Cyclone_Cli_Validation_Exception $ex) {
                echo $ex->getMessage();
                return;
            }

            $input_validator = new Cyclone_Cli_Input_Validator(
                            $_SERVER['argv'], $module
            );
            $input_validator->validate();
        }
    }

}
