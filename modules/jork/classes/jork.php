<?php


class JORK {

    const ONE_TO_ONE = 0;

    const ONE_TO_MANY = 1;

    const MANY_TO_MANY = 2;

    private static $_adapters = array();

    public static function from($entity) {
        $query = new JORK_Query_Select;
        $query->entity = $entity;
        return $query;
    }

    public static function adapter($name = 'default') {
        if ( ! array_key_exists($name, self::$_adapters)) {
            $cfg = Config::inst()->get('jork/'.$name);
            $class = 'JORK_Adapter_'.ucfirst($cfg['adapter']);
            self::$_adapters[$name] = new $class(DB::inst($cfg['db_inst']));
        }
        return self::$_adapters[$name];
    }

}