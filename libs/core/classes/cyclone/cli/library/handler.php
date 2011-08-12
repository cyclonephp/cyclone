<?php

/**
 * Library handler class of Cycle CLI.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.com>
 * @usedby cli.php
 */
class Cyclone_Cli_Library_Handler {

    private $_libraries;
    private static $_instance = null;

    private function __construct() {
        $this->_libraries = FileSystem::list_files('cli.php', true);
    }

    public static function inst() {
        if (self::$_instance === null) {
            self::$_instance = new Cyclone_Cli_library_Handler();
        }
        return self::$_instance;
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

    public function show_short_help() {
        foreach ($this->_libraries as $name => $library) {
            $desc = $this->get_desc($library);
            echo "\t$name\t " . $this->get_short_desc($desc) . PHP_EOL;
        }
    }

    public function get_library($library_name) {
        return new Cyclone_Cli_Library($library_name, $this->_libraries[$library_name]);
    }

    public function is_exists($library_name) {
        foreach ($this->_libraries as $name => $library) {
            if ($name == $library_name)
                return true;
        }
        return false;
    }

}

?>
