<?php


class DB {

    protected static $_instances = array();

    public static function inst($config = 'default') {
        if ( ! array_key_exists($config, self::$_instances)) {
            $cfg = Kohana::config('simpledb/'.$config);
            $class = 'DB_Adapter_'.ucfirst($cfg['adapter']);
            self::$_instances[$config] = new $class($cfg);
        }
        return self::$_instances[$config];
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
                return new DB_Expression_Custom($args[0]);
            case 2:
                return new DB_Expression_Unary($args[0], $args[1]);
            case 3:
                return new DB_Expression_Binary($args[0], $args[1], $args[2]);
        }
    }
    
}