<?php

namespace cyclone\cli;

/**
 * Library class of Cycle CLI.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.com>
 * @usedby cli.php
 * @package cyclone
 */
class Library {

    private $_name;
    private $_data;
    private $_curr_command = NULL;
    private $_curr_arg = NULL;

    /**
     * Set the library name and infos.
     */
    public function __construct($name, $data) {
        $this->_name = $name;
        $this->_data = $data;
    }

    public function get_name() {
        return $this->_name;
    }

    public function get_data() {
        return $this->_data;
    }

    /**
     * Check the library's validation.
     */
    public function validate() {
        $this->parse_library_info($this->_data);
    }

    /**
     * Check that the required library descriptors are defined.
     * If commands array exist and not empty call parsing on its values.
     */
    private function parse_library_info($data) {
        $this->_curr_command = NULL;
        $this->_curr_arg = NULL;
        if (!empty($data['descr']) || !empty($data['description'])) {
            if (!empty($data['commands'])) {
                foreach ($data['commands'] as $comm_name => $value) {
                    $this->_curr_command = $comm_name;
                    $this->parse_command($value);
                }
            } else {
                $this->throw_validation_exception(Errors::LIBRARY_COMMANDS_NOT_DEF, 102);
            }
        } else {
            $this->throw_validation_exception(Errors::LIBRARY_DESC_NOT_DEF, 101);
        }
    }

    /**
     * Check that the required command descriptors are defined.
     * When okay call parsing on its argument.
     */
    private function parse_command($data) {
        if (!empty($data['descr']) || !empty($data['description'])) {
            if (!empty($data['callback'])) {
                /** maybe it has no arguments */
                if (!empty($data['arguments'])) {
                    foreach ($data['arguments'] as $arg_name => $value) {
                        $this->_curr_arg = $arg_name;
                        $this->parse_command_args($value);
                    }
                } else {
                    /* command has no argument */
                    return;
                }
            } else {
                $this->throw_validation_exception(Errors::COMMAND_CALLBACK_NOT_DEF, 104);
            }
        } else {
            $this->throw_validation_exception(Errors::COMMAND_DESC_NOT_DEF, 103);
        }
    }

    private function parse_command_args($data) {

        if (!empty($data['alias'])) {
            if (!preg_match('/^-[a-zA-Z]$/', $data['alias'])) {
                $this->throw_validation_exception(Errors::ARG_ALIAS_BAD_FORMAT, 105);
            }
        }
        if (array_key_exists('parameter', $data)) {
            if (isset($data['parameter']) && !is_string($data['parameter'])) {
                $this->throw_validation_exception(Errors::ARG_PARAM_BAD_TYPE, 107);
            }
        } else {
            $this->throw_validation_exception(Errors::ARG_PARAM_NOT_DEFINED, 106);
        }
        if (array_key_exists('required', $data)) {
            if (!is_bool($data['required'])) {
                $this->throw_validation_exception(Errors::ARG_REQUIRED_BAD_TYPE, 108);
            } else if ($data['required'] && !isset($data['parameter'])) {
                $this->throw_validation_exception(Errors::ARG_REQUIRED_NO_SENSE, 109);
            }
        }
    }

    /**
     * Creates and throws a Cyclone_Cli_Validation_Exception.
     * @param string $error constant error message
     * @param int $code  the error code
     */
    private function throw_validation_exception($error, $code) {
        throw new ValidationException($error, $code, $this->_name, $this->_curr_command, $this->_curr_arg);
    }

}

?>
