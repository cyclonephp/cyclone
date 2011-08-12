<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
interface DB_Expression {

    public function compile_expr(DB_Compiler $adapter);

    /**
     * Returns TRUE if the expression contains the table $table_name,
     * otherwise FALSE.
     *
     * @param string $table_name
     * @return boolean
     */
    public function contains_table_name($table_name);
    
}
