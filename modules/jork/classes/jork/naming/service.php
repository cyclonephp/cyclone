<?php

class JORK_Naming_Service {

    public function  __construct() {
        
    }

    private $_table_aliases = array();

    /**
     * Stores name => JORK_Mapping_Schema pairs.
     * @var array
     */
    private $_entity_aliases = array();

    /**
     * @var JORK_Mapping_Schema
     */
    private $_implicit_root_schema;

    /**
     * Registers a new alias name. After an alias name is registered it can be
     * used as an explicit root entity class of a property chain.
     *
     * @param string $entity_class entity class name OR property chain
     * @param string $alias
     */
    public function set_alias($entity_class, $alias) {
        $this->_entity_aliases[$alias] = $this->get_schema($entity_class);
    }

    public function add_root_entity_class($entity_class, $alias = '') {
        if ('' == $alias) {
            $this->set_implicit_root($entity_class);
            return;
        }
    }

    public function set_implicit_root($class) {
        $this->_implicit_root_schema = JORK_Model_Abstract::schema_by_class($class);
    }

    /**
     * @param string $name a property chain or an alias name
     * @throws JORK_Exception if $name is not a valid name
     * @return JORK_Mapping_Schema or string
     */
    public function get_schema($name) {
        if ( ! array_key_exists($name, $this->_entity_aliases)) {
            $this->search_schema($name);
        }
        return $this->_entity_aliases[$name];
    }

    /**
     * Called if $name does not exist in $this->_aliases
     *
     * @param string $name a property chain or an alias name
     * @throws JORK_Exception if $name is not a valid name
     * @see JORK_Naming_Service::get_schema()
     */
    private function search_schema($name) {
        $segments = explode('.', $name);
        if (1 == count($segments)) {
            if (NULL == $this->_implicit_root_schema) {
                $this->_entity_aliases[$name] = JORK_Model_Abstract::schema_by_class($name);
                return;
            } else {
                foreach ($this->_implicit_root_schema->columns as $col_name => $col_def) {
                    if ($name == $col_name) {
                        $this->_entity_aliases[$name] = $col_def['type'];
                        return;
                    }
                }
                foreach ($this->_implicit_root_schema->components as $cmp_name => $cmp_def) {
                    if ($name == $cmp_name) {
                        $this->_entity_aliases[$name] = JORK_Model_Abstract::schema_by_class($col_def['class']);
                        return;
                    }
                }
            }
        } else {
            $walked_segments = array();
            if (NULL == $this->_implicit_root_schema) {
                if ( ! array_key_exists($segments[0], $this->_entity_aliases))
                    throw new JORK_Exception('invalid identifier: '.$name);
                $root_schema = $this->_entity_aliases[$segments[0]]; //explicit root entity class
                $walked_segments []= array_shift($segments);
            } else {
                $root_schema = $this->_implicit_root_schema;
            }
            $current_schema = $root_schema;
            foreach ($segments as $seg) {
                if (NULL == $current_schema) // only the last segment can be an atomic property
                    throw new JORK_Exception('invalid identifier: '.$name); // otherwise the search fails
                $found = FALSE;
                $walked_segments []= $seg;
                foreach ($current_schema->components as $cmp_name => $cmp_def) {
                    if ($cmp_name == $seg) {
                        $current_schema = JORK_Model_Abstract::schema_by_class($cmp_def['class']);
                        $this->_entity_aliases[implode('.', $walked_segments)] = $current_schema;
                        $found = TRUE; break;
                    }
                }
                foreach ($current_schema->columns as $col_name => $col_def) {
                    if ($col_name == $seg) {
                        $this->_entity_aliases[implode('.', $walked_segments)] = $col_def['type'];
                        //the schema in the next iteration will be NULL if column
                        // (atomic property) found
                        $current_schema = NULL;
                        $found = TRUE; break;
                    }
                }
                if ( ! $found)
                    throw new JORK_Exception('invalid identifier: '.$name);
            }
        }
    }

    public function table_alias($entity_class, $table_name) {
        if ( ! array_key_exists($entity_class, $this->_table_aliases)) {
            $this->_table_aliases[$entity_class] = array();
        }
        if ( ! array_key_exists($table_name, $this->_table_aliases[$entity_class])) {
            $this->_table_aliases[$entity_class][$table_name] = $table_name . '_'
                . count($this->_table_aliases[$entity_class][$table_name]);
        }
        
        $this->_table_aliases[$entity_class][$table_name];
    }

    
}