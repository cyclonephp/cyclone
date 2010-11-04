<?php

/**
 * This class is responsible for mapping any property chains represented by
 * JORK joins to DB joins and creating the result mappers.
 */
class JORK_Mapper_Component {

    protected $_alias_factory;

    protected  $_db_joins;

    /**
     * @param JORK_Alias_Factory $alias_factory must be the same instance as the
     * one which is used all over the query mapping process
     * @param array $db_joins the array of database joins. The new joins created
     * by the mapping process will be appended to this array.
     */
    public function  __construct(JORK_Alias_Factory $alias_factory,
            &$db_joins) {
        $this->_alias_factory = $alias_factory;
        $this->_db_joins = &$db_joins;
    }

    /**
     *
     * @param JORK_Schema $root_schema the previous schema
     * @param array $jork_joins
     * @return JORK_Mapper_Result
     * @see JORK_Mapper_Select::map()
     */
    public function build(JORK_Schema $root_schema, $jork_joins) {
        $mapper = new JORK_Mapper_Result;
        $mapper->entity_class = $root_schema->class;
        $mapper->table = $root_schema->table;
        foreach ($jork_joins as $join) {
            $comp_def = $root_schema->components[$join['component']];

            $this->component2join($root_schema, $join['component']);

            $next_schema = JORK::schema($comp_def['class']);
            $next_mapper = $this->build($next_schema, $join['nexts']);
            $next_mapper->component = $join['component'];
            $mapper->next_mappers []= $next_mapper;
        }
        return $mapper;
    }

    /**
     *
     * @param JORK_Schema $schema
     * @param string $component the name of the component to be joined
     */
    protected function component2join(JORK_Schema $schema, $component) {
        $comp_def = $schema->components[$component];
    }
}