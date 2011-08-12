<?php

/**
 * Data holder class that contains database-related metadata for Record classes.
 *
 * For every Record class a Record_Schema instance is created. It's values should be
 * initialized in the Record_Abstract::setup() implementations. The singleton
 * instances of the Record classes can access their related schema using their
 * $_schema attribute.
 */
class Record_Schema {

    /**
     * The name of the database connection to be used when working with the record
     *
     * @var string
     */
    public $database = 'default';

    /**
     * The name of the primary key column
     * 
     * @var string
     */
    public $primary_key = 'id';

    /**
     * @var string
     */
    public $table_name;

    /**
     * Columns name => DDL definition pairs
     * 
     * @var array
     */
    public $columns;

    /**
     * The name of the Record class that belongs to this Record_Schema instance.
     * It's value is created by Record_Abstract::_inst().
     *
     * @var string
     */
    public $class;
    
}