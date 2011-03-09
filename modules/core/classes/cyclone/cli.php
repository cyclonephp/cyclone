<?php

/**
 * Main class of Cyclone CLI.
 *
 * @author Zoltan Toth <zoltan.toth@cyclonephp.com>
 * @usedby index.php
 */
class Cyclone_CLI {
    const CALL_ERROR = "You mustn't call this method dircetly! Use cyphp instead.";

    public static function bootstrap() {
        if ($_SERVER['argv'][0] != 'cyphp') {
            echo self::CALL_ERROR."\n" ;
            return;
        }
        //TODO
    }

}