<?php

/**
 * Interface for classes that are able to compile DB_Query_* query builder
 * objects to SQL queries for a given SQL dialect.
 * 
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
interface DB_Compiler {

    public function compile_select(DB_Query_Select $query);

    public function compile_insert(DB_Query_Insert $query);

    public function compile_update(DB_Query_Update $query);

    public function compile_delete(DB_Query_Delete $query);
    
}
