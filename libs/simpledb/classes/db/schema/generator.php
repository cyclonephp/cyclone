<?php

/**
 * An implementing class should exist for each DBMS supported by CyclonePHP.
 *
 * Implementations are able to build proper DDL strings from the PHP objects that
 * represent database objects. These PHP objects are DB_Schema_Table and
 * DB_Schema_Column.
 */
interface DB_Schema_Generator {

    /**
     * Generates the database-specific DDL command for the table.
     *
     * @param DB_Schema_Table $table
     * @return string the generated DDL
     */
    public function ddl_create_table(DB_Schema_Table $table, $forced = FALSE);

    /**
     * Generates the database-specific DDL command for the table column.
     *
     * @param DB_Schema_Table $table
     * @return string the generated DDL
     */
    public function ddl_create_column(DB_Schema_Column $column);

}