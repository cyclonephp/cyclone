<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of validator
 *
 * @author zolee
 */
class Cyclone_Cli_Input_Validator {
    private $_input;
    private $_module;

    public function __construct($input, $module) {
        $this->_input = array_slice($input, 2);
        $this->_module = $module;
    }

    public function validate(){
        
    }

    private function show_module_help(){
        
    }
}
?>
