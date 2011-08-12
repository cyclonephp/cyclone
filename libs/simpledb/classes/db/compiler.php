<?php

/**
 * Interface for classes that are able to compile DB_Query_* query builder
 * objects to SQL queries for a given SQL dialect.
 *
 * Exactly one implementation belongs to each DBMS types and one instance to
 * each database adapters.
 * 
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 * @see DB::compiler()
 */
interface DB_Compiler {

    public function compile_select(DB_Query_Select $query);

    public function compile_insert(DB_Query_Insert $query);

    public function compile_update(DB_Query_Update $query);

    public function compile_delete(DB_Query_Delete $query);
    
}
