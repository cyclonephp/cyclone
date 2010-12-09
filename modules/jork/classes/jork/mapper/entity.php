<?php

class JORK_Mapper_Entity {

    /**
     * @var string the name of the table (alias) where to fetch the entity
     * properties from (used as a property name prefix)
     */
    protected $_table_alias;

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
    

    public function  __construct(JORK_Naming_Service $naming_srv
            , JORK_Query_Select $jork_query
            , DB_Query_Select $db_query
            , $select_item = NULL) {
        $this->_naming_srv = $naming_srv;
        $this->_jork_query = $jork_query;
        $this->_db_query = $db_query;

        $this->_entity_alias = $select_item;

        $this->_entity_schema = $this->_naming_srv->get_schema($this->_entity_alias);

        $this->add_tables();
    }

    protected function add_tables() {
        $this->_table_alias = $this->_naming_srv->table_alias($this->_entity_alias
                , $this->_entity_schema->table);
        $this->_db_query->tables []= array($this->_entity_schema->table, $this->_table_alias);
        foreach ($this->_entity_schema->columns as $col_name => $col_def) {
            $this->_db_query->columns []= $this->_table_alias.'.'
                    .(array_key_exists('db_column', $col_def) ? $col_def['db_column']
                        : $col_name);
            if (array_key_exists('table', $col_def)) { //we have got a secondary table
                //it must not be joined at this point, but TODO
            }
        }
    }

    /**
     * Here we don't take care about the property projections.
     * These must be merged one-by-ona at JORK_Mapper_Select->map_select()
     *
     * @param array $prop_chain the array representation of the property chain
     */
    public function merge_prop_chain(array $prop_chain) {
        
    }

    /**
     * Puts all atomic properties into the db query select list.
     * Called if the select list is empty.
     */
    public function select_all_atomics() {

    }
}