<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
abstract class DB_Query {

    public abstract function compile($database = 'default');

    public abstract function exec($database = 'default');

}
