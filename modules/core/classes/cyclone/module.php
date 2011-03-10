<?php

/**
 * Main class of Cyclone CLI.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.com>
 * @usedby index.php
 */

class Cyclone_Cli_Module {
    // short version of valid modules (module name and short description
    private $_modules_sort = null;

    function __construct() {
        $module_array = FileSystem::list_files('cli.php', TRUE);
        $i = 0;
        foreach ($module_array as $name => $module) {
            if ($this->validate_module($value) == TRUE) {
                $this->$_modules_sort[$i]['name'] = $name;
                $this->$_modules_sort[$i]['desc'] = $module['description'];
                $i++;
            }
        }
    }

    /**
     * Returns with an array of module_names and their short description.
     * @return array
     */
    public function get_modules_short() {
        return $this->modules_sort;
    }

    /**
     * Check the exist of the given module name.
     * @param string name of the searched modul
     * @return boolean
     */
    public function module_exist($module_name) {
        foreach ($this->$_modules_sort as $mod) {
            if ($mod['name'] === $module_name) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Check the module's validation.
     * @param array module
     * @return boolean
     */
    public function validate_module($module) {
        return TRUE;
        //TODO validate module
    }

}

?>
