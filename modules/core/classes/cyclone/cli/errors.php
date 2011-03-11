<?php

/**
 * Contains the error constants for cyclone CLI.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.com>
 * @usedby index.php
 */
class Cyclone_Cli_Errors {

    /** constatns of error texts */
    const CALL_ERROR = "!!You mustn't call this method dircetly! Use cyphp instead.";
    const MODULE_VALIDATION_FAILED = "!!MODULE VALIDATION FAILED: ";
    const NO_MODULE_NAME = "Module has no name.";
    const MORE_MODULES_IN_A_FILE = "Only one module definition allowed in a file.";
}

?>
