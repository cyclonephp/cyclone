<?php

namespace cyclone\cli;
/**
 * Contains the error constants for cyclone CLI.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.com>
 * @usedby cli.php
 * @package cyclone
 */
class Errors {
    const CALL_ERROR = '!!You mustn\'t call this method dircetly! Use cyphp instead.';
    const LIBRARY_VALIDATION_FAILED = '!!LIBRARY VALIDATION FAILED';
    /** Code: 101 */
    const LIBRARY_DESC_NOT_DEF = 'library description is not defined.';
    /** Code: 102 */
    const LIBRARY_COMMANDS_NOT_DEF = 'library commands are not defined.';
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
