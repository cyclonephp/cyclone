<?php


class JORK {

    const ONE_TO_ONE = 0;

    const ONE_TO_MANY = 1;

    const MANY_TO_MANY = 2;

    const MANY_TO_ONE = 3;

    /**
     * @var array the singleton adapter instances per config
     */
    private static $_adapters = array();

    /**
     * @param string $entity the name of the class to be queried
     * @return JORK_Query_Select
     */
    public static function from($entity) {
        $query = new JORK_Query_Select;
        list($enity_class, $alias) = JORK_Alias_Factory::entitydef_segments($entity);
        $query->entity = array(
            'entity_class' => $enity_class,
            'alias' => $alias
        );
        return $query;
    }

    /**
     * @param string $name adapter config name
     * @return JORK_Adapter
     */
    public static function adapter($name = 'default') {
        if ( ! array_key_exists($name, self::$_adapters)) {
            $cfg = Config::inst()->get('jork/'.$name);
            $class = 'JORK_Adapter_'.ucfirst($cfg['adapter']);
            self::$_adapters[$name] = new $class(DB::inst($cfg['db_inst']));
        }
        return self::$_adapters[$name];
    }

    /**
     *
     * @param string $class
     * @return JORK_Schema
     */
//    public static function schema($class) {
//        static $schemas = array();
//        if ( ! array_key_exists($class, $schemas)) {
//            $schemas[$class] = JORK_Model_Abstract::schema_by_class($class);
//        }
//        return $schemas[$class];
//    }

}