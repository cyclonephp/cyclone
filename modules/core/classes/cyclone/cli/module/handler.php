<?php

/**
 * Module handler class of Cycle CLI.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.com>
 * @usedby cli.php
 */
class Cyclone_Cli_Module_Handler {

    private $_modules;
    private static $_instance = null;

    private function __construct() {
        $this->_modules = FileSystem::list_files('cli.php', true);
    }

    public static function inst() {
        if (self::$_instance === null) {
            self::$_instance = new Cyclone_Cli_Module_Handler();
        }
        return self::$_instance;
    }

    public function show_short_help() {
        foreach ($this->_modules as $name => $module) {
            if (!empty($module['description'])) {
                echo "\t$name\t ". strtok($module['description'], "\n") . PHP_EOL;
            } else if (!empty($module['desc'])) {
                echo "\t$name\t ". strtok($module['desc'], "\n") . PHP_EOL;
            }
        }
    }

    public function get_module($module_name) {
        return new Cyclone_Cli_Module($module_name, $this->_modules[$module_name]);
    }

    public function is_exists($module_name) {
        foreach ($this->_modules as $name => $module) {
            if ($name == $module_name)
                return true;
        }
        return false;
    }

}

?>
