<?php

class JORK_Mapper_Entity {

    /**
     * @var JORK_Alias_Factory the alias factory to be used all over the query
     * mapping process
     */
    protected $_alias_factory;

    /**
     * @var DB_Query_Select the database query to be built
     */
    protected  $_db_query;

    /**
     * @var string the name of the table (alias) where to fetch the entity
     * properties from (used as a property name prefix)
     */
    protected $_table;

    /**
     * @var JORK_Schema
     */
    protected $_entity_schema;

    /**
     * @var string
     */
    protected $_entity_alias;

    /**
     * @var the next mappers to be executed on the same row
     */
    protected $_next_mappers = array();

    public function  __construct(JORK_Query_Select $jork_select
            , JORK_Alias_Factory $alias_factory
            , DB_Query_Select $db_query) {
        $schema = JORK::schema($jork_select->entity['entity_class']);
        $this->_alias_factory = $alias_factory;
        $this->_db_query = $db_query;

        $this->_entity_schema = $schema;
        $this->_entity_alias = $jork_select->entity['alias'];
        $this->_table = $this->_alias_factory->for_table($schema->table);
        
        $this->_db_query->tables = array(
            array($schema->table, $this->_table)
        );
        $this->create_next_mappers($jork_select->joins);
    }

    /**
     * Creates the appropriate mapper objects for the next properties
     * in the selected property chain.
     * 
     * @param array $joins the next joins
     * @see JORK_Mapper_Entity::__construct()
     * @see JORK_Mapper_Component::__construct()
     */
    protected function create_next_mappers($joins) {
        foreach ($joins as $join) {
            $this->_next_mappers []= JORK_Mapper_Component::factory($this
                , $join
                , $this->_alias_factory
                , $this->_db_query
            );
        }
    }
}