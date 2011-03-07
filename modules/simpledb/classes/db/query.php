<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
abstract class DB_Query {

    public abstract function compile($database = 'default');

    public abstract function exec($database = 'default');

    /**
     * @return DB_Query_Prepared
     */
    public function prepare($database = 'default') {
        $db = DB::inst($database);
        $sql = $db->compile($this);
        return $db->prepare($sql);
    }

}
