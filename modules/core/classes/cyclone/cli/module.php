<?php

/**
 * Module class of Cycle CLI.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.com>
 * @usedby index.php
 */
class Cyclone_Cli_Module {

    private $_name;
    private $_data;
    private $_curr_command = NULL;
    private $_curr_arg = NULL;

    /**
     * Set the module name and infos.
     */
    public function __construct($name, $data) {
        $this->_name = $name;
        $this->_data = $data;
    }

    public function get_name() {
        return $this->_name;
    }

    public function get_short_desc() {
        return strtok($data['description'], "\n");
    }

    public function get_long_desc() {
        $res = "";
        $i = 0;
        $tok = strtok($data['description'], "\n");
        while ($tok !== false) {
            $tok = strtok($data['description']);
            if ($i > 1) {
                $res .= $tok;
            }
            $i++;
        }
        return $res;
    }

    public function get_data() {
        return $this->_data;
    }

    /**
     * Check the module's validation.
     */
    public function validate() {
        $this->parse_module_info();
    }

    /**
     * Check that the required module descriptors are defined.
     * If commands array exist and not empty call parsing on its values.
     */
    private function parse_module_info() {
        $this->_curr_command = NULL;
        $this->_curr_arg = NULL;
        if (!empty($data['desc']) || !empty($data['description'])) {
            if (!empty($data['command'])) {
                foreach ($data['command'] as $comm_name => $value) {
                    $this->_curr_command = $comm_name;
                    $this->parse_command($value);
                }
            } else {
                $this->throw_validation_exception(Cyclone_Cli_Errors::MODULE_COMMANDS_NOT_DEF);
            }
        } else {
            $this->throw_validation_exception(Cyclone_Cli_Errors::MODULE_DESC_NOT_DEF);
        }
    }

    /**
     * Check that the required command descriptors are defined.
     * When okay call parsing on its argument.
     */
    private function parse_command($data) {
        if (!empty($data['desc']) || !empty($data['description'])) {
            if (!empty($data['callback'])) {
                /** maybe it has no arguments */
                if (!empty($data['arguments'])) {
                    $this->parse_command_args($data['arguments']);
                    foreach ($data['arguments'] as $arg_name => $value) {
                        $this->_curr_arg = $arg_name;
                        $this->parse_command_arg($value);
                    }
                }
                else
                /* command has no argument */
                    return;
            } else {
                $this->throw_validation_exception(Cyclone_Cli_Errors::COMMAND_CALLBACK_NOT_DEF);
            }
        } else {
            $this->throw_validation_exception(Cyclone_Cli_Errors::COMMAND_DESC_NOT_DEF);
        }
    }

    private function parse_command_arg($data) {
        if (!empty($data['alias'])) {
            if (!preg_match("^-.$", $data['alias'])) {
                $this->throw_validation_exception(Cyclone_Cli_Errors::ARG_ALIAS_BAD_FORMAT);
            }
        }
        if (!empty($data['parameter'])) {
            if (!isset($data['parameter']) && !is_string($data['paramter'])) {
                $this->throw_validation_exception(Cyclone_Cli_Errors::ARG_PARAM_BAD_TYPE);
            }
        } else {
            $this->throw_validation_exception(Cyclone_Cli_Errors::ARG_PARAM_NOT_DEFINED);
        }
        if (!empty($data['required'])) {
            if (!is_bool($data['required'])) {
                $this->throw_validation_exception(Cyclone_Cli_Errors::ARG_REQUIRED_BAD_TYPE);
            } else if ($data['required'] && !isset($data['parameter'])) {
                $this->throw_validation_exception(Cyclone_Cli_Errors::ARG_REQUIRED_NO_SENSE);
            }
        }
    }

    private function throw_validation_exception($error) {
        throw new Cyclone_Cli_Validation_Exception($error, $this->_name, $this->_curr_command, $this->_curr_arg);
    }

}

?>
