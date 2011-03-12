<?php

/**
 * Contains the error constants for cyclone CLI.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.com>
 * @usedby index.php
 */
class Cyclone_Cli_Errors {
    const CALL_ERROR = "!!You mustn't call this method dircetly! Use cyphp instead.";
    const MODULE_VALIDATION_FAILED = "!!MODULE VALIDATION FAILED";
    const MODULE_DESC_NOT_DEF = "module description is not defined.";
    const MODULE_COMMANDS_NOT_DEF = "module commands are not defined.";
    const COMMAND_DESC_NOT_DEF = "command description is not defined.";
    const COMMAND_CALLBACK_NOT_DEF = "command callback is not defined.";
}

?>
