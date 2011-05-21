<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
class DB {

    private static $_compilers = array();

    private static $_executors = array();

    private static $_executor_prepareds = array();

    private static $_connectors = array();

    private static $_schema_generators = array();

    /**
     * @param string $config config file name
     * @return DB_Compiler
     */
    public static function compiler($config = 'default') {
        if ( ! array_key_exists($config, self::$_compilers)) {
            $cfg = Config::inst()->get('simpledb/'.$config);
            $class = 'DB_Compiler_'.$cfg['adapter'];
            self::$_compilers[$config] = new $class($cfg, DB::connector($config)->db_conn);
        }
        return self::$_compilers[$config];
    }

    /**
     * @param string $config config file name
     * @return DB_Executor
     */
    public static function executor($config = 'default') {
        if ( ! array_key_exists($config, self::$_executors)) {
            $cfg = Config::inst()->get('simpledb/'.$config);
            $class = 'DB_Executor_'.$cfg['adapter'];
            self::$_executors[$config] = new $class($cfg, DB::connector($config)->db_conn);
        }
        return self::$_executors[$config];
    }

    /**
     * @param string $config config file name
     * @return DB_Executor_Prepared
     */
    public static function executor_prepared($config = 'default') {
        if ( ! array_key_exists($config, self::$_executor_prepareds)) {
            $cfg = Config::inst()->get('simpledb/'.$config);
            $class = 'DB_Executor_Prepared_'.$cfg['adapter'];
            self::$_executor_prepareds[$config] = new $class($cfg, DB::connector($config)->db_conn);
        }
        return self::$_executor_prepareds[$config];
    }

    /**
     * @param string $config config file name
     * @return DB_Connector
     */
    public static function connector($config = 'default') {
        if ( ! array_key_exists($config, self::$_connectors)) {
            $cfg = Config::inst()->get('simpledb/'.$config);
            $class = 'DB_Connector_'.$cfg['adapter'];
            self::$_connectors[$config] = new $class($cfg);
        }
        return self::$_connectors[$config];
    }

    /**
     * @param string $config config file name
     * @return DB_Schema_Generator
     */
    public static function schema_generator($config = 'default') {
        if ( ! array_key_exists($config, self::$_schema_generators)) {
            $cfg = Config::inst()->get('simpledb/'.$config);
            $class = 'DB_Schema_Generator_'.$cfg['adapter'];
            self::$_schema_generators[$config] = new $class($cfg);
        }
        return self::$_schema_generators[$config];
    }

    /**
     * Helper factory method for custom SQL queries.
     *
     * @param string $sql
     * @return DB_Query_Custom
     */
    public static function query($sql) {
        return new DB_Query_Custom($sql);
    }

    /**
     * Helper factory method for SQL SELECT queries.
     *
     * @return DB_Query_Select
     */
    public static function select() {
        $query = new DB_Query_Select;
        $args = func_get_args();
        $query->columns_arr($args);
        return $query;
    }

    /**
     * Helper factory method for SQL SELECT DISTINCT queries.
     *
     * @return DB_Query_Select
     */
    public static function select_distinct() {
        $query = new DB_Query_Select;
        $query->distinct = TRUE;
        $args = func_get_args();
        $query->columns_arr($args);
        return $query;
    }

    /**
     * Helper factory method for SQL UPDATE statements.
     *
     * @param string $table the table name to be updated
     * @return DB_Query_Update
     */
    public static function update($table = null) {
        $query = new DB_Query_Update;
        $query->table = $table;
        return $query;
    }

    /**
     * Helper factory method for SQL INSERT statements.
     *
     * @param string $table the table to insert into
     * @return DB_Query_Insert
     */
    public static function insert($table = null) {
        $query = new DB_Query_Insert;
        $query->table = $table;
        return $query;
    }

    /**
     * Helper factory method for SQL DELETE statements.
     *
     * @param string $table the table to delete from
     * @return DB_Query_Delete
     */
    public static function delete($table = null) {
        $query = new DB_Query_Delete;
        $query->table = $table;
        return $query;
    }

    public static function expr() {
        return self::create_expr(func_get_args());
    }

    /**
     * @param array $args
     * @return DB_Expression
     */
    public static function create_expr($args) {
        switch (count($args)) {
            case 1:
                if (is_array($args[0])) {
                    return new DB_Expression_Set($args[0]);
                }
                return new DB_Expression_Custom($args[0]);
            case 2:
                return new DB_Expression_Unary($args[0], self::create_nullexpr($args[1]));
            case 3:
                return new DB_Expression_Binary(self::create_nullexpr($args[0])
                        , $args[1]
                        , self::create_nullexpr($args[2]));
        }
    }

    protected static function create_nullexpr($arg) {
        if (null === $arg) {
            return new DB_Expression_Custom('NULL');
        } else {
            return $arg;
        }
    }

    public static function clear_connections() {
        foreach (self::$_connectors as $connector) {
            $connector->disconnect();
        }
        self::$_compilers = array();
        self::$_connectors = array();
        self::$_executors = array();
        self::$_executor_prepareds = array();
    }

    public static function esc($param) {
        return new DB_Expression_Param($param);
    }

    public static function param($name = '?') {
        return new DB_Expression_Custom($name);
    }
    
}
