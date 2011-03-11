<?php

/**
 * Module handler class of Cycle CLI.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.com>
 * @usedby index.php
 */
class Cyclone_Cli_Module {

    // short version of valid modules (module name and short description
    private static $_modules_short = NULL;
    // modules
    private static $_modules = NULL;
    // is the _modules_short and _modules variables initalized
    private static $_initalized = FALSE;
    // currently parsed module
    private static $_curr_module = NULL;
    // currently parsed command
    private static $_curr_command = NULL;

    /** constanst for  depth  of module parsing */
    const MODULE_NAME = 1;
    const MODULE_INFO = 2;
    const COMMAND_NAME = 3;
    const COMMAND_INFO = 4;
    const COMMAND_ARGS = 5;
    const COMMAND_ARG_INFO = 6;

    /**
     * Load the modules and initalize the core variables.
     */
    private static function initalize() {
        self::$_modules = FileSystem::list_files('cli.php', TRUE);
        $i = 0;
        foreach (self::$_modules as $name => $module) {
            if (self::$validate_module($module) == TRUE) {
                $this->_modules_short[$i]['name'] = $name;
                $this->_modules_short[$i]['desc'] = $module['description'];
                $i++;
            }
        }
        if (self::$_modules_short === NULL) {
            self::$_modules_short = array();
        }
        self::$_initalized = TRUE;
    }

    /**
     * Returns with an array of module_names and their short description.
     * @return array
     */
    public static function get_modules_short() {
        if (!self::$_initalized) {
            self::initalize();
        }
        return $this->_modules_short;
    }

    /**
     * Check the exist of the given module name.
     * @param string $module_name name of the searched modul
     * @return boolean
     */
    public static function module_exist($module_name) {
        if (!self::$_initalized) {
            self::initalize();
        }
        foreach ($this->_modules_short as $mod) {
            if ($mod['name'] === $module_name) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Check the module's validation.
     * @param array $module module
     * @return boolean
     */
    public static function validate_module($module) {
        foreach ($array as $key => $value) {

        }
        return TRUE;
        //TODO validate module
    }

    /**
     * Parse the module
     * @param array $data data for parsing
     * @param integer $depth depth of parsing
     */
    private static function parse_module($data, $depth) {
        switch ($depth) {
            case self::MODULE_NAME: {
                    $run = 0;
                    foreach ($data as $name => $value) {
                        if (run > 0) {
                            self::show_validation_error(Cyclone_Cli_Errors::MORE_MODULES_IN_A_FILE);
                            return;
                        }
                        if (empty($name)) {
                            self::show_validation_error(Cyclone_Cli_Errors::NO_MODULE_NAME);
                            return;
                        }
                        self::parse_module($value, self::MODULE_INFO);
                        $run++;
                    }
                    break;
                }
            case self::MODULE_INFO: {
                    break;
                }
            case self::COMMAND_NAME: {
                    break;
                }
            case self::COMMAND_INFO: {

                    break;
                }
            case self::COMMAND_ARGS: {
                    break;
                }
            case self::COMMAND_ARG_INFO: {
                    break;
                }
            default: {
                    echo self::ERR_MODULE_VALIDATION_FAILED . PHP_EOL;
                    break;
                }
        }
    }

    /**
     *
     * @param <type> $error error message
     */
    private static function show_validation_error($error) {
        echo self::ERR_MODULE_VALIDATION_FAILED . $error . PHP_EOL;
    }

}

?>
