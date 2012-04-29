<?php

namespace cyclone\cli;

/**
 * Validation Exception class of Cyclone CLI.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.org>
 * @usedby cli.php
 */
class ValidationException extends \Exception {

    public function __construct($error, $code = NULL, $library_name = NULL, $command_name = NULL, $arg_name = NULL) {
        $this->code = $code;
        $msg = Errors::LIBRARY_VALIDATION_FAILED . PHP_EOL .
                "\tat library: $library_name" . PHP_EOL;
        if (!empty($command_name)) {
            $msg .= "\tat command: $command_name" . PHP_EOL;
        }
        if (!empty($arg_name)) {
            $msg .= "\tat argument: $arg_name". PHP_EOL;
        }
        $msg .= "\tcause: $error" . PHP_EOL . PHP_EOL;
        $this->message = $msg;
    }

}

?>
