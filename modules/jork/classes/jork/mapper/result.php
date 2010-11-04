<?php


class JORK_Mapper_Result {

    /**
     * @var string the name of the table (alias) where to fetch the entity
     * properties from (used as a property name prefix)
     */
    public $table;

    /**
     * @var string the component of the parent entity where the new entity must
     * be put
     */
    public $component;

    /**
     *
     * @var string the name of the entity class to be instantiated
     */
    public $entity_class;

    /**
     * @var the next mappers to be executed on the same row
     */
    public $next_mappers = array();

    public function map_row($row_data, JORK_Model_Abstract $parent_entity) {
        //TODO implement
    }
}