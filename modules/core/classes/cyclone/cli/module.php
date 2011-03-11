<?php

/**
 * Module handler class of Cycle CLI.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.com>
 * @usedby index.php
 */
class Cyclone_Cli_Module {

    /** short version of valid modules (module name and short description */
    private $_modules_short = NULL;
    /** valid modules */
    private $_modules = NULL;
    /** currently parsed module */
    private $_curr_module = NULL;
    /** currently parsed command */
    private $_curr_command = NULL;

    /** constanst for  depth  of module parsing */
    const MODULE_INFO = 0;
    const COMMAND_NAME = 1;
    const COMMAND_INFO = 2;
    const COMMAND_ARGS = 3;
    const COMMAND_ARG_INFO = 4;

    /**
     * Load the modules and initalize the core variables.
     */
    public function __construct() {
        $modules = FileSystem::list_files('cli.php', TRUE);
        $i = 0;
        foreach ($modules as $name => $module) {
            if ($this->validate_module($module, $name)) {
                $this->_modules[$name] = $module;
                $this->_modules_short[$i]['name'] = $name;
                $this->_modules_short[$i]['desc'] = strtok($module['description'], "\n"); // long desc, concat to short
                $i++;
            }
        }
        if ($this->_modules_short === NULL) {
            $this->_modules_short = array();
        }
    }

    /**
     * Returns with an array of module_names and their short description.
     * @return array
     */
    public function get_modules_short() {
        return $this->_modules_short;
    }

    /**
     * Check the exist of the given module name.
     * @param string $module_name name of the searched modul
     * @return boolean
     */
    public function module_exist($module_name) {
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
    public function validate_module($module, $module_name) {
        $this->_curr_module = $module_name;
        return $this->parse_module($module, self::MODULE_INFO);
    }

    /**
     * Parse the module
     * @param array $data data for parsing
     * @param integer $depth depth of parsing
     */
    private function parse_module($data, $depth) {

        switch ($depth) {
            /**
             * Check that the required module descriptors are defined. If commands array exist call parsing on it.
             */
            case self::MODULE_INFO: {
                    $this->_curr_command = NULL;
                    if (array_key_exists("desc", $data) || array_key_exists("description", $data)) {
                        if (array_key_exists("commands", $data)) {
                            return $this->parse_module($data['commands'], self::COMMAND_NAME);
                        } else {
                            $this->show_module_error(Cyclone_Cli_Errors::MODULE_COMMANDS_NOT_DEF);
                            return FALSE;
                        }
                    } else {
                        $this->show_module_error(Cyclone_Cli_Errors::MODULE_DESC_NOT_DEF);
                        return FALSE;
                    }
                }
            /**
             * Check that there are commands, if any exist call parsing on each command.
             */
            case self::COMMAND_NAME: {
                    $this->_curr_command = NULL;
                    if (count(array_keys($data)) == 0) {
                        $this->show_module_error(Cyclone_Cli_Errors::MODULE_COMMANDS_NOT_DEF);
                        return FALSE;
                    }
                    foreach ($data as $comm_name => $value) {
                        $this->_curr_command = $comm_name;
                        if (!$this->parse_module($value, self::COMMAND_INFO)) {
                            return FALSE;
                        }
                    }
                    return TRUE;
                }
                /**
                 * Check that the required command descriptors are defined.
                 * When okay call parsing on its argument.
                 */
            case self::COMMAND_INFO: {
                    if (array_key_exists("desc", $data) || array_key_exists("description", $data)) {
                        if (array_key_exists("callback", $data)) {
                            $this->parse_module($data['arguments'], self::COMMAND_ARGS);
                        } else {
                            $this->show_module_error(Cyclone_Cli_Errors::COMMAND_CALLBACK_NOT_DEF);
                            return FALSE;
                        }
                    } else {
                        $this->show_module_error(Cyclone_Cli_Errors::COMMAND_DESC_NOT_DEF);
                        return FALSE;
                    }
                }
            case self::COMMAND_ARGS: {
                    return TRUE;
                    // SOME TODO
                    break;
                }
            case self::COMMAND_ARG_INFO: {
                    break;
                }
            default: {
                    echo Cyclone_Cli_Errors::MOD_VAL_FAIL . PHP_EOL;
                    break;
                }
        }
    }

    /**
     *
     * @param <type> $error error message
     */
    private function show_module_error($error) {
        echo Cyclone_Cli_Errors::MODULE_VALIDATION_FAILED . PHP_EOL;
        echo "\tin module: " . $this->_curr_module . PHP_EOL;
        if (!empty($this->_curr_command)) {
            echo "\tat command: " . $this->_curr_command . PHP_EOL;
        }
        echo "\tcause: " . $error . PHP_EOL . PHP_EOL;
    }

}

?>
