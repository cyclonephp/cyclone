<?php

/**
 * This class is responsible for mapping any property chains represented by
 * JORK joins to DB joins and creating the result mappers.
 */
abstract class JORK_Mapper_Component extends JORK_Mapper_Entity {

    /**
     * @var JORK_Mapper_Entity
     */
    protected $_parent_mapper;

    /**
     * @var string the component of the parent entity where the new entity must
     * be put
     */
    protected $_component;

    public function map_row($row_data, JORK_Model_Abstract $parent_entity) {
        //TODO implement
    }

    public function  __construct(JORK_Mapper_Entity $parent_mapper
            , $join_def
            , JORK_Alias_Factory $alias_factory
            , DB_Query_Select $db_query) {
        $this->_alias_factory = $alias_factory;
        $this->_db_query = $db_query;
        
        $this->create_next_mappers($join_def['nexts']);
    }

    
    public static function factory(JORK_Mapper_Entity $parent_mapper
            , $join_def
            , JORK_Alias_Factory $alias_factory
            , DB_Query_Select $db_query) {
        $comp_def = $parent_mapper->_entity_schema->components[$join_def['component']];

        $impls = array(
            JORK::ONE_TO_ONE => 'JORK_Mapper_Component_OneToOne',
            JORK::ONE_TO_MANY => 'JORK_Mapper_Component_OneToMany',
            JORK::MANY_TO_ONE => 'JORK_Mapper_Component_ManyToOne',
            JORK::MANY_TO_MANY => 'JORK_Mapper_Component_ManyToMany'
        );

        if ( ! array_key_exists($comp_def['type'], $impls))
            throw new JORK_Exception("unknown component type: {$comp_def['type']}");

        $class = $impls[$comp_def['type']];

        return new $class($parent_mapper
            , $join_def
            , $alias_factory
            , $db_query
        );
    }

    protected abstract function component2join();

}