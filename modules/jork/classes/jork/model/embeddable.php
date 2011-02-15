<?php


abstract class JORK_Model_Embeddable {

    private static $_instances = array();

    protected static function _inst($class) {
        if ( ! array_key_exists($class, self::$_instances)) {
            self::$_instances[$class] = new $class;
        }
        return self::$_instances[$class];
    }

    public abstract function setup(JORK_Mapping_Schema_Embeddable $schema);

}