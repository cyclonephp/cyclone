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

    private function parse_command($command) {
        echo "parse command TODO";
    }

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

    private function command_exists($command) {
        foreach ($this->_data['commands'] as $cmd_name => $value) {
            if ($cmd_name === $command) {
                return true;
            }
        }
        return false;
    }

    private function get_desc($from) {
        if (!empty($from['description'])) {
            return $from['description'];
        } else {
            return $from['descr'];
        }
    }

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

    private function get_long_desc($description) {
        $tokenized = explode("\n", $description);
        if (count($tokenized) > 1 && empty($tokenized[1])) {
            strtok($description, "\n");
            return strtok($description, "");
        } else {
            return $description;
        }
    }

    private function show_command_list() {
        foreach ($this->_data['commands'] as $cmd_name => $value) {
            echo "\t $cmd_name \t" . $this->get_short_desc($this->get_desc($value)) . PHP_EOL;
        }
    }

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
