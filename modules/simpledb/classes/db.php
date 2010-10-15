<?php


class DB {

    protected static $_instances = array();

    /**
     *
     * @param string $config config file name
     * @return DB_Adapter
     */
    public static function inst($config = 'default') {
        if ( ! array_key_exists($config, self::$_instances)) {
            $cfg = Kohana::config('simpledb/'.$config);
            $class = 'DB_Adapter_'.ucfirst($cfg['adapter']);
            self::$_instances[$config] = new $class($cfg);
        }
        return self::$_instances[$config];
    }

    public static function query($sql) {
        return new DB_Query_Custom($sql);
    }

    public static function select() {
        $query = new DB_Query_Select;
        $query->columns_arr(func_get_args());
        return $query;
    }

    public static function update($table = null) {
        $query = new DB_Query_Update;
        $query->table = $table;
        return $query;
    }

    public static function insert($table = null) {
        $query = new DB_Query_Insert;
        $query->table = $table;
        return $query;
    }

    public static function delete($table = null) {
        $query = new DB_Query_Delete;
        $query->table = $table;
        return $query;
    }

    public static function expr() {
        return self::create_expr(func_get_args());
    }

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
        self::$_instances = array();
    }

    public static function esc($param) {
        return new DB_Expression_Param($param);
    }
    
}