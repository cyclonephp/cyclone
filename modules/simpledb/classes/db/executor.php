<?php

/**
 * Interface for classes that are able to execute an SQL query on a given
 * DBMS type, using the appropriate php functions and methods.
 * 
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
interface DB_Executor {

    public function exec_select($select_sql);

    public function exec_insert($insert_sql, $return_insert_id);

    public function exec_update($update_sql);

    public function exec_delete($delete_sql);
}
