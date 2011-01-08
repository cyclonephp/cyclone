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
                    if ( ! ($embedded_schema_provider instanceof JORK_Model_Embeddable))
                        throw new JORK_Exception ('unknown component type: '.$v);
                    $embedded_schema_provider->append_schema($inst->_schema);
                }
            }
            self::$_instances[$classname] = $inst;
        }
        return self::$_instances[$classname];
    }

    /**
     * @param string $class
     * @return JORK_Mapping_Schema
     */
    public static function schema_by_class($class) {
        if ( ! array_key_exists($class, self::$_instances)) {
            self::_inst($class);
        }
        return self::$_instances[$class]->_schema;
    }

    /**
     * Gets the mapping schema of the current entity.
     * 
     * @return JORK_Mapping_Schema
     */
    public function schema() {
        if ( ! array_key_exists(get_class($this), self::$_instances)) {
            self::_inst(get_class($this));
        }
        return self::$_instances[get_class($this)]->_schema;
    }

    protected $_schema;

    protected $_atomics = array();

    protected $_components = array();

    protected $_persistent = FALSE;

    /**
     * @return mixed the primary key of the entity
     */
    public function pk() {
        $pk = $this->schema()->primary_key();
        return array_key_exists($pk, $this->_atomics)
                ? $this->_atomics[$pk]
                : NULL;
    }

    public function init_component_collections(&$prop_names) {
        foreach (array_diff_key($prop_names, $this->_components) as $prop => $dummy) {
            if ( ! array_key_exists($prop, $this->_components)) {
                $this->_components[$prop] = array('value' => new ArrayObject);
            }
        }
    }

    public function populate_atomics($atomics) {
        $this->_atomics = $atomics;
    }

    public function set_components($components) {
        foreach ($components as $k => $v) {
            $this->_components[$k] = array(
                'value' => $v,
                'persistent' => TRUE
            );
        }
    }

    public function add_to_component_collections($components) {
        foreach ($components as $prop_name => $new_comp) {
            $this->_components[$prop_name]['value'][$new_comp->pk()]= $new_comp;
        }
    }

    public function  __get($key) {
        $schema = $this->schema();
        if (array_key_exists($key, $schema->columns)) {
            return array_key_exists($key, $this->_atomics)
                    ? $this->_atomics[$key]
                    : NULL;
        }
        if (array_key_exists($key, $schema->components)) {
            return array_key_exists($key, $this->_components)
                    ? $this->_components[$key]['value']
                    : NULL;
        }
        throw new JORK_Exception("class '{$schema->class}' has no property '$key'");
    }

    public function __set($key, $val) {
        $schema = $this->schema();
        if (array_key_exists($key, $schema->columns)) {
            if ( ! array_key_exists($key, $this->_atomics)) {
                $this->_atomics[$key] = array(
                    'value' => $val
                );
            } else {
                $this->_atomics[$key]['value'] = $val;
            }
            $this->_persistent = FALSE;
        } elseif (array_key_exists($key, $schema->components)) {
            if ( ! array_key_exists($key, $this->_components)) {
                $this->_components[$key] = array(
                    'value' => $val,
                    'persistent' => FALSE
                );
            } else {
                $this->_components[$key]['value'] = $val;
                $this->_components[$key]['persistent'] = FALSE;
            }
            $this->_persistent = FALSE;
        } else
            throw new JORK_Exception("class '{$schema->class}' has no property '$key'");
    }

}