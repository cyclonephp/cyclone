<?php

/**
 * Exception class of Cyclone CLI.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.com>
 * @usedby index.php
 */
class Cyclone_Cli_Validation_Exception extends Exception {
    private $msg;

     public function __construct($error, $module_name, $command_name, $arg_name) {
        $this->msg = Cyclone_Cli_Errors::MODULE_VALIDATION_FAILED . PHP_EOL .
                "\tat module: " . $module_name . PHP_EOL;
        if (!empty($command_name)) {
            $this->msg .= "\tat command: " . $command_name . PHP_EOL;
        }
         if (!empty($arg_name)) {
            $this->msg .= "\tat argumentum: " . $arg_name . PHP_EOL;
        }
        $this->msg . "\tcause: " . $error . PHP_EOL . PHP_EOL;
    }

    public function getMessage(){
        return $this->msg;
    }
}

?>
