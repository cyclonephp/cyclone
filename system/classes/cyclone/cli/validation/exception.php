<?php

/**
 * Validation Exception class of Cyclone CLI.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.com>
 * @usedby cli.php
 */
class Cyclone_Cli_Validation_Exception extends Exception {

    public function __construct($error, $code, $library_name, $command_name, $arg_name) {
        $this->code = $code;
        $msg = Cyclone_Cli_Errors::LIBRARY_VALIDATION_FAILED . PHP_EOL .
                "\tat library: $library_name" . PHP_EOL;
        if (!empty($command_name)) {
            $msg .= "\tat command: $command_name" . PHP_EOL;
        }
        if (!empty($arg_name)) {
            $msg .= "\tat argumentum: $arg_name". PHP_EOL;
        }
        $msg .= "\tcause: $error" . PHP_EOL . PHP_EOL;
        $this->message = $msg;
    }
   

    /*public function getMessage() {
        return $this->_msg;
    }

    public function getCode() {
        return $this->_error_code;
    }*/

}

?>
