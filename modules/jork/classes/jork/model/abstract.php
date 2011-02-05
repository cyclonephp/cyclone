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
     * A set of listeners to be notified when the primary key of the model changes.
     *
     * @var array<JORK_Model_Collection>
     */
    protected $_pk_change_listeners = array();

    /**
     * Indicates if the saving process of the entity has already been started
     * or not. It's used to prevent infinite recursion in the case of saving
     * bidirectional relationships.
     *
     * @var boolean
     */
    protected $_save_in_progress = FALSE;

    /**
     * @return mixed the primary key of the entity
     */
    public function pk() {
        $pk = $this->schema()->primary_key();
        return array_key_exists($pk, $this->_atomics)
                ? $this->_atomics[$pk]['value']
                : NULL;
    }

    public function add_pk_change_listener($listener) {
        $this->_pk_change_listeners []= $listener;
    }

    public function init_component_collections(&$prop_names) {
        foreach (array_diff_key($prop_names, $this->_components) as $prop => $dummy) {
            if ( ! array_key_exists($prop, $this->_components)) {
                $this->_components[$prop] = array('value' =>
                    JORK_Model_Collection::for_component($this, $prop));
            }
        }
    }

    public function populate_atomics($atomics) {
        foreach ($atomics as $k => $v) {
            $this->_atomics[$k] = array(
                'value' => $v,
                'persistent' => TRUE
            );
        }
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
            $this->_components[$prop_name]['value'][$new_comp->pk()] = $new_comp;
        }
    }

    /**
     *
     * @param string $key
     * @param JORK_Model_Abstract $val
     * @param array $comp_schema
     */
    protected function update_component_fks_reverse($key, $val, $comp_schema) {
        $remote_schema = $val->schema()->components[$comp_schema['mapped_by']];
        switch($remote_schema['type']) {
            case JORK::ONE_TO_MANY:
                $this->_atomics[$remote_schema['join_column']]['value'] = array_key_exists('inverse_join_column', $remote_schema)
                    ? $val->_atomics[$remote_schema['inverse_join_column']]
                    : $val->pk();
                $this->_atomics[$remote_schema['join_column']]['persistent'] = FALSE;
                break;
            case JORK::ONE_TO_ONE:
                $val->_atomics[$remote_schema['join_column']]['value'] = array_key_exists('inverse_join_column', $remote_schema)
                    ? $this->_atomics[$remote_schema['inverse_join_column']]
                    : $this->pk();
                $val->_atomics[$remote_schema['join_column']]['persistent'] = FALSE;
                break;
        }
    }

    /**
     * Updates the foreign keys when the value of a component changes.
     *
     * @param string $key the name of the component
     * @param JORK_Model_Abstract $val
     * @see JORK_Model_Abstract::__set()
     */
    protected function update_component_fks($key, $val) {
        $schema = $this->schema();
        $comp_schema = $schema->components[$key];
        if (array_key_exists('mapped_by', $comp_schema)) {
            $this->update_component_fks_reverse($key, $val, $comp_schema);
            return;
        }
        switch ($comp_schema['type']) {
            case JORK::MANY_TO_ONE:
                $this->_atomics[$comp_schema['join_column']]['value'] = array_key_exists('inverse_join_column', $comp_schema)
                    ? $val->_atomics[$comp_schema['inverse_join_column']]['value']
                    : $val->pk();
                $this->_atomics[$comp_schema['join_column']]['persistent'] = FALSE;
                break;
            case JORK::ONE_TO_ONE:
                $this->_atomics[$comp_schema['join_column']]['value'] = array_key_exists('inverse_join_column', $comp_schema)
                    ? $val->_atomics[$comp_schema['inverse_join_column']]
                    : $val->pk();
                $this->_atomics[$comp_schema['join_column']]['persistent'] = FALSE;
                break;
        }
    }

    public function  __get($key) {
        $schema = $this->schema();
        if (array_key_exists($key, $schema->columns)) {
            return array_key_exists($key, $this->_atomics)
                    ? $this->_atomics[$key]['value']
                    : NULL;
        }
        if (array_key_exists($key, $schema->components)) {
            if (array_key_exists($key, $this->_components))
                // return if the component value is already initialized
                return $this->_components[$key]['value'];
            if ($schema->is_to_many_component($key)) {
                // it's a to-many relation and initialize an
                // empty component collection
                $this->_components[$key] = array(
                    'value' => JORK_Model_Collection::for_component($this, $key)
                );
            } else {
                $this->_components[$key] = array(
                    'persistent' => TRUE, // default NULL must not be persisted
                    'value' => NULL
                );
            }
            return $this->_components[$key]['value'];
                   
        }
        throw new JORK_Exception("class '{$schema->class}' has no property '$key'");
    }

    public function __set($key, $val) {
        $schema = $this->schema();
        if (array_key_exists($key, $schema->columns)) {
            $this->_atomics[$key]['value'] = $val;
            $this->_atomics[$key]['persistent'] = FALSE;
            $this->_persistent = FALSE;
        } elseif (array_key_exists($key, $schema->components)) {
            if ( ! array_key_exists($key, $this->_components)) {
                $this->_components[$key] = array(
                    'value' => $val,
                    'persistent' => FALSE
                );
                $this->update_component_fks($key, $val);
            } else {
                $this->_components[$key]['value'] = $val;
                $this->_components[$key]['persistent'] = FALSE;
            }
            $this->_persistent = FALSE;
        } else
            throw new JORK_Exception("class '{$schema->class}' has no property '$key'");
    }

    /**
     * 
     */
    public function insert() {
        $schema = $this->schema();
        $insert_sqls = JORK_Query_Cache::inst(get_class($this))->insert_sql();
        $ins_tables = array();
        $values = array();
        $prim_table = NULL;
        foreach ($schema->columns as $col_name => $col_def) {
            if (array_key_exists($col_name, $this->_atomics)) {
                if ($this->_atomics[$col_name]['persistent'] == FALSE) {
                    $ins_table = array_key_exists('table', $col_def)
                            ? $col_def['table']
                            : $schema->table;
                    if ( ! in_array($ins_table, $ins_tables)) {
                        $ins_tables []= $ins_table;
                    }
                    if ( ! array_key_exists($ins_table, $values)) {
                        $values[$ins_table] = array();
                    }
                    $col = array_key_exists('db_column', $col_def)
                            ? $col_def['db_column']
                            : $col_name;
                    $values[$ins_table][$col] = $this->_atomics[$col_name]['value'];

                    // In fact the value is not yet persistent, but we assume
                    // that no problem will happen until the insertions
                    $this->_atomics[$col_name]['persistent'] = TRUE;
                }
            } elseif (array_key_exists('primary', $col_def)) {
                // The primary key does not exist in the record
                // therefore we save the table name for the table
                // containing the primary key
                $prim_table = $schema->table_name_for_column($col_name);
            }
        }
        if (NULL === $prim_table) {
            foreach ($values as $tbl_name => $ins_values) {
                $insert_sqls[$tbl_name]->values = array($ins_values);
                $insert_sqls[$tbl_name]->exec($schema->db_conn);
            }
        } else {
            foreach ($values as $tbl_name => $ins_values) {
                $insert_sqls[$tbl_name]->values = array($ins_values);
                $tmp_id = $insert_sqls[$tbl_name]->exec($schema->db_conn);
                if ($prim_table == $tbl_name) {
                    $this->_atomics[$schema->primary_key()]['value'] = $tmp_id;
                    foreach ($this->_pk_change_listeners as $listener) {
                        $listener->notify_pk_creation($this);
                    }
                }
            }
        }
        // The insert process finished, the entity is now persistent
        $this->_persistent = TRUE;
    }

    public function update() {
        
    }

    public function save() {
        if (!$this->_save_in_progress) {
            $this->_save_in_progress = TRUE;
            if (!$this->_persistent) {
                if ($this->pk() === NULL) {
                    $this->insert();
                } else {
                    $this->update();
                }
            }
            $this->_save_in_progress = FALSE;
        }
    }

}