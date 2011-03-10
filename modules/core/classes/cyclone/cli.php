<?php

/**
 * Main class of Cyclone CLI.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.com>
 * @usedby index.php
 */
class Cyclone_CLI {
    const CALL_ERROR = "You mustn't call this method dircetly! Use cyphp instead.";
    const INTRO = "For module help type: cyphp <module_name> ";

    public static function bootstrap() {

        if ($_SERVER['argv'][0] != 'cyphp' && $_SERVER['argv'][0] != './cyphp') {
            echo self::CALL_ERROR . "\n";
            return;
        }

        $param_num = count($_SERVER['argv']);
        $cli_module = new Cyclone_Cli_Module();
        $modules_short = $cli_module->get_modules_short();

        if ($param_num == 1 || $cli_module->module_exist($_SERVER['argv'][1]) === FALSE) {
            echo self::INTRO . "\n\n";
            foreach ($modules_short as $mod) {
                echo "\t" . $mod['name'] . "\t" . $mod['desc'] . "\n";
            }
        } else {
            // TODO
            echo "OK" . "\n";
        }
    }

}
