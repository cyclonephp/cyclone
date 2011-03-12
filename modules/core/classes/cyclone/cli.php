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
        if ($_SERVER['argv'][0] != 'cyphp' && $_SERVER['argv'][0] != './cyphp') {
            echo Cyclone_Cli_Errors::CALL_ERROR . PHP_EOL;
            return;
        }

        $module_handler = new Cyclone_Cli_Module();
        $param_num = count($_SERVER['argv']);
        $modules_short = $module_handler->get_modules_short();

        if ($param_num == 1 || $module_handler->module_exist($_SERVER['argv'][1]) === FALSE) {
            echo self::INTRO . PHP_EOL;
            foreach ($modules_short as $mod) {
                echo "\t" . $mod['name'] . "\t" . $mod['desc'] . PHP_EOL;
            }
        } else {
            // TODO
            echo "OK" . PHP_EOL;
        }
    }

}
