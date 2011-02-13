<?php

class JORK_Mapper_Component_Embedded extends JORK_Mapper_Entity {

    /**
     *
     * @var JORK_Mapper_Entity
     */
    protected $_parent_mapper;

    protected $_comp_name;

    /**
     *
     * @var JORK_Mapping_Schema_Embeddable
     */
    protected $_comp_schema;


    public function __construct($parent_mapper, $comp_name, $select_item) {
        $this->_parent_mapper = $parent_mapper;
        $this->_comp_name = $comp_name;
        $this->_entity_alias = $select_item;
        $this->_entity_schema = $this->_parent_mapper
                ->_entity_schema->components[$comp_name];
        $this->_naming_srv = $this->_parent_mapper->_naming_srv;
        $this->_db_query = $this->_parent_mapper->_db_query;
    }

    protected function add_atomic_property($prop_name, &$prop_schema) {

        if (in_array($prop_name, $this->_result_atomics))
                return;

        $tbl_name = $this->_parent_mapper->_entity_schema->table;

        if ( ! array_key_exists($tbl_name, $this->_parent_mapper->_table_aliases)) {
            $tbl_alias = $this->_parent_mapper->add_table($tbl_name);
        }
        $tbl_alias = $this->_parent_mapper->_table_aliases[$tbl_name];

        $col_name = array_key_exists('column', $prop_schema)
                ? $prop_schema['column']
                : $prop_name;

        $full_column = $tbl_alias.'.'.$col_name;
        $full_alias = $tbl_alias.'_'.$col_name;
        $this->_db_query->columns []= array($full_column, $full_alias);
        $this->_result_atomics[$full_alias] = $prop_name;
//
//        if ($prop_name == $this->_entity_schema->primary_key()) {
//            $this->_result_primary_key_column = $full_alias;
//        }
    }
    
    
}