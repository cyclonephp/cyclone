<?php

/**
 * Maps a jork select to a db select.
 */
class JORK_Mapper_Select {

    protected $_jork_query;

    protected $_db_query;

    protected $_mapper;

    protected $_alias_factory;

    public function  __construct(JORK_Query_Select $jork_query) {
        $this->_jork_query = $jork_query;
        $this->_db_query = new DB_Query_Select;
        $this->_alias_factory = new JORK_Alias_Factory;
    }

    public function map() {
        $this->map_entity();

        $component_mapper = new JORK_Mapper_Component($this->_alias_factory
                , &$this->_db_query->joins);

        $this->_mapper = $component_mapper->build(JORK::schema($this->_jork_query->entity['entity_class'])
                , $this->_jork_query->joins);

        return array($this->_db_query, $this->_mapper);
    }

    protected function map_entity() {
        $entity_schema = JORK::schema($this->_jork_query->entity['entity_class']);

        $table_alias = $this->_alias_factory->for_table($entity_schema->table);

        $this->_db_query->tables = array(
            array($entity_schema->table
                , $table_alias)
        );

    }

    protected function map_joins(ArrayObject $joins, JORK_Schema $parent_schema) {
//        foreach($joins as $join) {
//            $component = $join['component'];
//            $comp_def = $parent_schema->components[$component];
//            JORK_Mapper_Component::component2join($comp_def
//                    , $this->_alias_factory
//                    , &$this->_db_query->joins
//                    , &$this->_metadata->properties);
//        }
    }

}