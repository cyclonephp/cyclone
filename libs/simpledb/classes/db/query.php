<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
interface DB_Query {

    public function compile($database = 'default');

    public function exec($database = 'default');

    /**
     * @return DB_Query_Prepared
     */
    public function prepare($database = 'default');

}
