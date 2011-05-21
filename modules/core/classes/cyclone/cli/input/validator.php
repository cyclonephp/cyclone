<?php

/**
 * Input validator and callbacker of Cyclone CLI.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.com>
 * @usedby cli.php
 */
class Cyclone_Cli_Input_Validator {

    private $_input;
    private $_module;
    private $_data;

    public function __construct($input, $module) {
        $this->_input = array_slice($input, 2);
        $this->_module = $module;
        $this->_data = $module->get_data();
    }

    /**
     * Validates the user input and if all ok then call the corresponding function.
     */
    public function validate() {
        if (count($this->_input) == 0) {
            $this->show_module_help();
        } else {
            if ($this->_input[0] == "help" && count($this->_input) > 1) {
                if ($this->command_exists($this->_input[1])) {
                    $command = $this->_data['commands'][$this->_input[1]];
                    $this->show_command_help($command);
                } else {
                    echo "!!No such command as " . $this->_input[1] . PHP_EOL;
                    $this->show_module_help();
                }
            } else if ($this->_input[0] == "help" && count($this->_input) == 1) {
                $this->show_module_help();
            } else {
                if ($this->command_exists($this->_input[0])) {
                    $command = $this->_data['commands'][$this->_input[0]];
                    $this->parse_command($command);
                } else {
                    echo "!!No such command as " . $this->_input[0] . PHP_EOL;
                    $this->show_module_help();
                }
            }
        }
    }

    /**
     * Return with the argumentum name, if it exists.
     * If the argumentum was an alias, return with the "true" name.
     * If no such argumentum like $var, then return with null;
     * @param string $var input argumentum
     * @param array $arguments argumentum array of a module command
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
     * @param string $arg_name existing argumentum
     * @param array $arguments argumentum array of a module command
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
     * @param array $cbarray callback array
     * @param array $arguments argumentum array of a module command
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
     * This command choosed in the input, it parses the rest of the user input.
     * @param array $command choosed command's array
     */
    private function parse_command($command) {
        $cbarray = array();
        $input_args = $this->slice_input();
        $i = 0;

        if (count($input_args) == 0) {
            $this->suplement_callback_array($cbarray, $command['arguments']);
            call_user_func($command['callback'], $cbarray);
            return;
        }

        while ($i < count($input_args)) {
            $arg_name = $this->get_argument_name($input_args[$i], $command['arguments']);
            if ($arg_name != null) {
                if ($this->argument_has_param($arg_name, $command['arguments'])) {
                    ++$i;
                    // test that, the next argumentum is a parameter
                    if (empty($input_args[$i]) || preg_match('/^-/', $input_args[$i])) {
                        echo "!!$arg_name needs a parameter." . PHP_EOL;
                        return;
                    } else {
                        $cbarray[$arg_name] = $input_args[$i];
                    }
                } else {
                    $cbarray[$arg_name] = true;
                }
            } else {
                echo '!!Paramter given without specified argument OR no such argument. Cause: ' . $input_args[$i] . PHP_EOL;
                return;
            }
            ++$i;
        }
        
        $this->suplement_callback_array($cbarray, $command['arguments']);
        call_user_func($command['callback'], $cbarray);
    }

    /**
     * Displays the given command's help.
     * @param array $command module command
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
     * Check that the given command exists in the module.
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
     * Displays the modules command list in this format: <cmd_name> <short_description>
     */
    private function show_command_list() {
        foreach ($this->_data['commands'] as $cmd_name => $value) {
            echo "\t $cmd_name \t" . $this->get_short_desc($this->get_desc($value)) . PHP_EOL;
        }
    }

    /**
     * Displays the module's help.
     */
    private function show_module_help() {
        // echo 'Module:' . PHP_EOL . $this->_module->get_name() . PHP_EOL . PHP_EOL;
        $long_desc = $this->get_long_desc($this->get_desc($this->_data));
        echo 'Description:' . PHP_EOL . $long_desc . PHP_EOL . PHP_EOL;
        echo 'type <module> help <command> for detailed command help.' . PHP_EOL . PHP_EOL;
        echo 'Available commands:' . PHP_EOL;
        $this->show_command_list();
    }

}

?>
