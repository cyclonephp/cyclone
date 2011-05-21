<?php

class DB_Schema_Table {

    public static function for_record_schema(Record_Schema $schema) {
        $rval = new DB_Schema_Table;
        $rval->database = $schema->database;
        $rval->table_name = $schema->table_name;
        foreach ($schema->columns as $colname => $ddl) {
            $col = new DB_Schema_Column;
            $col->name = $colname;
            $col->ddl = $ddl;
            $rval->columns[$colname] = $col;
        }
        return $rval;
    }

    /**
     * The name of the database connection configuration to be used when working
     * with this instance (eg. when creating DDL from it)
     *
     * @var string
     */
    public $database;

    /**
     * The name of the database table
     *
     * @var string
     */
    public $table_name;

    /**
     * Column name => DB_Schema_Column pairs
     *
     * @var array
     */
    public $columns = array();
}
