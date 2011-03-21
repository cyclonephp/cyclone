<?php

/**
 * Contains the error constants for cyclone CLI.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.com>
 * @usedby index.php
 */
class Cyclone_Cli_Errors {
    const CALL_ERROR = '!!You mustn\'t call this method dircetly! Use cyphp instead.';
    const MODULE_VALIDATION_FAILED = '!!MODULE VALIDATION FAILED';
    /** Code: 101 */
    const MODULE_DESC_NOT_DEF = 'module description is not defined.';
    /** Code: 102 */
    const MODULE_COMMANDS_NOT_DEF = 'module commands are not defined.';
    /** Code: 103 */
    const COMMAND_DESC_NOT_DEF = 'command description is not defined.';
    /** Code: 104 */
    const COMMAND_CALLBACK_NOT_DEF = 'command callback is not defined.';
    /** Code: 105 */
    const ARG_ALIAS_BAD_FORMAT = 'alias has bad format, good format is -<alphabet> , example: -a';
    /** Code: 106 */
    const ARG_PARAM_NOT_DEFINED = 'argument parameter must be defined.';
    /** Code: 107 */
    const ARG_PARAM_BAD_TYPE = 'type of argument parameter must be NULL or String.';
    /** Code: 108 */
    const ARG_REQUIRED_BAD_TYPE = 'type of argument required must be boolean.';
    /** Code: 109 */
    const ARG_REQUIRED_NO_SENSE = 'argument paramter is NULL, argument required has no sense to be null in this case.';
}

?>
