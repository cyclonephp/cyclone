<?php

namespace cyclone\cli;

/**
 * Input validator and callbacker of Cyclone CLI.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.org>
 * @usedby cli.php
 * @package cyclone
 */
class InputValidator {

    private $_input;
    private $_library;
    private $_data;

    public function __construct($input, $library) {
        $this->_input = array_slice($input, 2);
        $this->_library = $library;
        $this->_data = $library->get_data();
    }

    /**
     * Validates the user input and if all ok then call the corresponding function.
     */
    public function validate() {
        if (count($this->_input) == 0) {
            $this->show_library_help();
        } else {
            if ($this->_input[0] == "help" && count($this->_input) > 1) {
                if ($this->command_exists($this->_input[1])) {
                    $command = $this->_data['commands'][$this->_input[1]];
                    $this->show_command_help($command);
                } else {
                    echo "!!No such command as " . $this->_input[1] . PHP_EOL;
                    $this->show_library_help();
                }
            } else if ($this->_input[0] == "help" && count($this->_input) == 1) {
                $this->show_library_help();
            } else {
                if ($this->command_exists($this->_input[0])) {
                    $command = $this->_data['commands'][$this->_input[0]];
                    return $this->parse_command($command);
                } else {
                    echo "!!No such command as " . $this->_input[0] . PHP_EOL;
                    $this->show_library_help();
                    return 1;
                }
            }
        }
        return 0;
    }

    /**
     * Return with the argument name, if it exists.
     * If the argument was an alias, return with the "true" name.
     * If no such argumentum like $var, then return with null;
     * @param string $var input argument
     * 
     * @param array $arguments argumentum array of a library command
     */
    private function get_argument_name($var, $arguments) {
        foreach ($arguments as $arg_name => $value) {
            if ($var === $arg_name) {
                return $var;
            }
            if (!empty($value['alias'])) {
                if ($var === $value['alias']) {
                    return $arg_name;
                }
            }
        }
        return null;
    }

    /**
     * Check that, the given argumentum has parameter.
     * 
     * @param string $arg_name existing argumentum
     * @param array $arguments argumentum array of a library command
     */
    private function argument_has_param($arg_name, $arguments) {
        if (!empty($arguments[$arg_name]['parameter'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Fills the callback array with the not passed arguments.
     * 
     * @param array $cbarray callback array
     * @param array $arguments argumentum array of a library command
     */
    private function suplement_callback_array($cbarray, $arguments) {
        foreach ($arguments as $arg_name => $value) {
            if (empty($cbarray[$arg_name])) {
                $cbarray[$arg_name] = null;
            }
        }
    }

    /**
     * Slice the --arg=par arguments.
     * @param array $input user input
     */
    private function slice_input() {
        if (count($this->_input) == 1) {
            return array();
        }
        $tmp = '';
        $length = count($this->_input) - 1;
        for ($i = 1; $i < $length; $i++) {
            $tmp .= $this->_input[$i] . ' ';
        }
        $tmp .= $this->_input[$length];
        $tmp = str_replace('=', ' ', $tmp);
        return explode(' ', $tmp);
    }

    /**
     * Adds the default values of arguments of <code>$comand</code> to
     * <code>$parsed_args</code> after the explicitly passed CLI arguments
     * are parsed.
     *
     * @param array $parsed_args
     * @param array $command
     * @usedby Cyclone_CLI_Validator::parse_command()
     */
    private function add_defaults(&$parsed_args, $command) {
        if ( ! isset($command['arguments']))
            return;
        
        foreach ($command['arguments'] as $arg_name => $arg_details) {
            if ( ! isset($parsed_args[$arg_name])) {
                $parsed_args[$arg_name] = isset($arg_details['default'])
                        ? $arg_details['default']
                        : NULL;
            }
            if (isset($arg_details['required']) && $arg_details['required']
                    && $parsed_args[$arg_name] === NULL)
                throw new InputException("required argument '$arg_name' is missing.");
        }
    }

    /**
     * This command choosed in the input, it parses the rest of the user input.
     * 
     * @param array $command choosed command's array
     */
    private function parse_command($command) {
        $cbarray = array();
        $input_args = $this->slice_input();
        $i = 0;

        if (count($input_args) == 0) {
            $this->add_defaults($cbarray, $command);
            return call_user_func($command['callback'], $cbarray);
        }

        while ($i < count($input_args)) {
            $arg_name = $this->get_argument_name($input_args[$i], $command['arguments']);
            if ($arg_name != null) {
                if ($this->argument_has_param($arg_name, $command['arguments'])) {
                    ++$i;
                    // test if the next argument is a parameter
                    if (empty($input_args[$i]) || preg_match('/^-/', $input_args[$i])) {
                        echo "!!$arg_name needs a parameter." . PHP_EOL;
                        return;
                    } else {
                        $cbarray[$arg_name] = $input_args[$i];
                    }
                } else {
                    $cbarray[$arg_name] = true;
                }
            } elseif ($compact_args = $this->parse_compact_args($input_args, $i, $command['arguments'])) {
                $cbarray += $compact_args;
            } else {
                echo '!!Paramter given without specified argument OR no such argument. Cause: ' . $input_args[$i] . PHP_EOL;
                return;
            }
            ++$i;
        }
        $this->add_defaults($cbarray, $command);
        return call_user_func($command['callback'], $cbarray);
    }

    private function parse_compact_args($input_args, &$i, $command_args) {
        $rval = array();
        $compact_arg = $input_args[$i];
        if ($compact_arg{0} != '-')
            return FALSE;
        
        $compact_arg = substr($compact_arg, 1); // cutting the leading '-'

        for ($c = 0; $c < strlen($compact_arg); ++$c) {
            $arg_name = $this->get_argument_name('-' . $compact_arg{$c}, $command_args);
            if (NULL === $arg_name)
                return FALSE;
            if ($this->argument_has_param($arg_name, $command_args)) {
                if ($c != strlen($compact_arg) - 1 // only the last alias can have parameter
                        || empty($input_args[$i + 1]) // the next argument should exist
                        || ($input_args[$i + 1]{0} == '-')) // and it shouldn't start with '-' since that notates the next parameter
                    return FALSE;
                $rval[$arg_name] = $input_args[++$i];
            } else {
                $rval[$arg_name] = TRUE;
            }
        }
        return $rval;
    }

    /**
     * Displays the given command's help.
     * @param array $command library command
     */
    private function show_command_help($command) {
        echo $this->get_desc($command) . PHP_EOL;
        if (array_key_exists('arguments', $command)) {
            echo 'Arguments:' . PHP_EOL;
            foreach ($command['arguments'] as $name => $value) {
                if (!empty($value['parameter'])) {
                    echo "\t$name=" . $value['parameter'];
                    if (!empty($value['alias'])) {
                        echo ' ,' . $value['alias'] . ' ' . $value['parameter'];
                    }
                } else {
                    echo "\t$name";
                    if (!empty($value['alias'])) {
                        echo ' ,' . $value['alias'];
                    }
                }
                echo "\t" . $this->get_desc($value) . PHP_EOL;
            }
        } else {
            echo "This command has no arguments." . PHP_EOL;
        }
    }

    /**
     * Check that the given command exists in the library.
     * @param string $command command name
     * @return boolean
     */
    private function command_exists($command) {
        foreach ($this->_data['commands'] as $cmd_name => $value) {
            if ($cmd_name === $command) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return with the description.
     * @param array $from
     * @return string the description
     */
    private function get_desc($from) {
        if (!empty($from['description'])) {
            return $from['description'];
        } else {
            return $from['descr'];
        }
    }

    /**
     * Returns with the short description if exists.
     * @param string $description
     * @return string short description
     */
    private function get_short_desc($description) {
        $tokenized = explode("\n", $description);
        if (count($tokenized) > 1 && empty($tokenized[1])) {
            return $tokenized[0];
        } else {
            if (count($tokenized) == 1) {
                return $tokenized[0];
            } else {
                return "";
            }
        }
    }

    /**
     * Returns with the long description.
     * @param string $description
     * @return string long description
     */
    private function get_long_desc($description) {
        $tokenized = explode("\n", $description);
        if (count($tokenized) > 1 && empty($tokenized[1])) {
            strtok($description, "\n");
            return strtok($description, "");
        } else {
            return $description;
        }
    }

    /**
     * Displays the libraries command list in this format: <cmd_name> <short_description>
     */
    private function show_command_list() {
        foreach ($this->_data['commands'] as $cmd_name => $value) {
            echo "\t $cmd_name \t" . $this->get_short_desc($this->get_desc($value)) . PHP_EOL;
        }
    }

    /**
     * Displays the library's help.
     */
    private function show_library_help() {
        // echo 'library:' . PHP_EOL . $this->_library->get_name() . PHP_EOL . PHP_EOL;
        $long_desc = $this->get_long_desc($this->get_desc($this->_data));
        echo 'Description:' . PHP_EOL . $long_desc . PHP_EOL . PHP_EOL;
        echo 'type <library> help <command> for detailed command help.' . PHP_EOL . PHP_EOL;
        echo 'Available commands:' . PHP_EOL;
        $this->show_command_list();
    }

}

?>
