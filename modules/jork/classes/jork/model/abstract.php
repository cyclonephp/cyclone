<?php


abstract class JORK_Model_Abstract {

    protected abstract function setup();

    private static $_instances = array();

    protected static function _inst($classname) {
        if ( ! array_key_exists($classname, self::$_instances)) {
            $inst = new $classname;
            $inst->_schema = new JORK_Mapping_Schema;
            $inst->_schema->class = $classname;
            $inst->setup();
            foreach ($inst->_schema->components as $k => $v) {
                if (is_string($v)) { //embeddable component
                    $embedded_schema_provider = new $v;
                    if ( ! ($v instanceof JORK_Model_Embeddable))
                        throw new JORK_Exception ('unknown component type: '.$v);
                    $v->append_schema($inst->_schema);
                }
            }
            self::$_instances[$classname] = $inst;
        }
        return self::$_instances[$classname];
    }

    protected function schema() {
        if ( ! array_key_exists(get_class($this), self::$_instances)) {
            self::_inst(get_class($this));
        }
        return self::$_instances[get_class($this)]->_schema;
    }

    public static function schema_by_class($class) {
        if ( ! array_key_exists($class, self::$_instances)) {
            self::_inst($class);
        }
        return self::$_instances[$class]->_schema;
    }

}