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

    protected $_comp_name;

    private $_is_joined = FALSE;

    public function  __construct(JORK_Mapper_Entity $parent_mapper, $comp_name, $select_item) {
        parent::__construct($parent_mapper->_naming_srv
                , $parent_mapper->_jork_query
                , $parent_mapper->_db_query
                , $select_item);
        $this->_parent_mapper = $parent_mapper;
        $this->_comp_name = $comp_name;
    }

   

    
    public static function factory(JORK_Mapper_Entity $parent_mapper
            , $comp_name, $select_item) {
        $comp_def = $parent_mapper->_entity_schema->components[$comp_name];

        $impls = array(
            JORK::ONE_TO_ONE => 'JORK_Mapper_Component_OneToOne',
            JORK::ONE_TO_MANY => 'JORK_Mapper_Component_OneToMany',
            JORK::MANY_TO_ONE => 'JORK_Mapper_Component_ManyToOne',
            JORK::MANY_TO_MANY => 'JORK_Mapper_Component_ManyToMany'
        );

        if (array_key_exists('mapped_by', $comp_def)) {
            $remote_schema = JORK_Model_Abstract::schema_by_class($comp_def['class']);

            $remote_comp_def = $remote_schema->get_property_schema($comp_def['mapped_by']);

            $class = $impls[$remote_comp_def['type']];

            return new $class($parent_mapper, $comp_name, $select_item);

        } else {
            if ( ! array_key_exists($comp_def['type'], $impls))
                throw new JORK_Exception("unknown component type: {$comp_def['type']}");
            $class = $impls[$comp_def['type']];

            return new $class($parent_mapper, $comp_name, $select_item);
        }

    }
    protected abstract function comp2join();

    protected abstract function comp2join_reverse();

    protected function parent_to_from() {
        $comp_schema = $this->_parent_mapper->_entity_schema->components[$this->_comp_name];
        $join_col = $comp_schema['join_column'];
        $tbl_name = $this->_parent_mapper->_entity_schema->table_name_for_column($join_col);
        $this->_parent_mapper->add_table($tbl_name);
    }

    protected function  add_table($tbl_name) {
        if ( ! $this->_is_joined) {
            $this->_is_joined = true;
            if (empty($this->_db_query->tables)) {
                $this->parent_to_from();
            }
            if (array_key_exists('mapped_by',
                    $this->_parent_mapper->_entity_schema->components[$this->_comp_name])) {
                $this->comp2join_reverse();
            } else {
                $this->comp2join();
            }
        }
    }

}