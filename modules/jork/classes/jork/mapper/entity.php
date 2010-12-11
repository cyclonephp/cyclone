<?php

class JORK_Mapper_Entity {

    /**
     * @var array
     */
    protected $_table_aliases = array();

    /**
     * @var JORK_Schema
     */
    protected $_entity_schema;

    /**
     * @var string
     */
    protected $_entity_alias;

    /**
     *
     * @var DB_Query_Select
     */
    protected $_db_query;

    /**
     * @var JORK_Query_Select
     */
    protected $_jork_query;

    /**
     * The naming service to be used and passed on to the next mappers
     *
     * @var JORK_Naming_Service
     */
    protected $_naming_srv;

    /**
     * the next mappers to be executed on the same row
     *
     * @var array<JORK_Mapper_Component>
     */
    protected $_next_mappers = array();

    public function map_row($db_row) {
        //TODO implement
    }
    

    public function  __construct(JORK_Naming_Service $naming_srv
            , JORK_Query_Select $jork_query
            , DB_Query_Select $db_query
            , $select_item = NULL) {
        $this->_naming_srv = $naming_srv;
        $this->_jork_query = $jork_query;
        $this->_db_query = $db_query;

        $this->_entity_alias = $select_item;

        $this->_entity_schema = $this->_naming_srv->get_schema($this->_entity_alias);

    }

    /**
     * @param string $tbl_name
     * @return string the generated alias
     */
    protected function add_table($tbl_name) {
        $tbl_alias = $this->_table_aliases[$tbl_name]
                = $this->_naming_srv->table_alias($this->_entity_alias, $tbl_name);
        $this->_db_query->tables []= array($tbl_name, $tbl_alias);
        return $tbl_alias;
    }


    /**
     * Adds an atomic property join to the db query
     *
     * @param string $property
     */
    protected  function add_atomic_property($prop_name, &$prop_schema) {
        $tbl_name = array_key_exists('table', $prop_schema)
                ? $prop_schema['table']
                : $this->_entity_schema->table;

        if ( !array_key_exists($tbl_name, $this->_table_aliases)) {
            $tbl_alias = $this->add_table($tbl_name);
        } else {
            $tbl_alias = $this->_table_aliases[$tbl_name];
        }
        $col_name = array_key_exists('db_column', $prop_schema) 
                ? $prop_schema['db_column']
                : $prop_name;
        
        $this->_db_query->columns []= $tbl_alias.'.'.$col_name;
        
    }

    /**
     *
     * @param string $prop_name
     * @param array $prop_schema
     * @return JORK_Mapper_Entity
     */
    protected function get_component_mapper($prop_name, $prop_schema) {
        if (array_key_exists($prop_name, $this->_next_mappers))
            return $this->_next_mappers[$prop_name];

        $select_item = $this->_entity_alias == '' ? $prop_name
                : $this->_entity_alias.'.'.$prop_name;

        return $this->_next_mappers[$prop_name] =
                JORK_Mapper_Component::factory($this, $prop_name, $select_item);
    }

    /**
     * Here we don't take care about the property projections.
     * These must be merged one-by-ona at JORK_Mapper_Select->map_select()
     *
     * @param array $prop_chain the array representation of the property chain
     * @throws JORK_Schema_Exception
     */
    public function merge_prop_chain(array $prop_chain) {
        $root_prop = array_shift($prop_chain);
        $schema = $this->_entity_schema->get_property_schema($root_prop);
        if ( ! empty($prop_chain)) {
            if ( ! array_key_exists('class', $schema))
                throw new JORK_Syntax_Exception('only the last item of a property
                    chain can be an atomic property');
            $next_mapper = $this->get_component_mapper($root_prop, $schema);
            $next_mapper->merge_prop_chain($prop_chain);
            $next_mapper->select_all_atomics();
        } else {
            if (array_key_exists('class', $schema)) {
                $next_mapper = $this->get_component_mapper($root_prop, $schema);
                $next_mapper->select_all_atomics();
            } else {
                $this->add_atomic_property($root_prop, $schema);
            }
        }
    }

    /**
     * Puts all atomic properties into the db query select list.
     * Called if the select list is empty.
     */
    public function select_all_atomics() {
        foreach ($this->_entity_schema->columns as $prop_name => $prop_schema) {
            $this->add_atomic_property($prop_name, $prop_schema);
        }
    }
}