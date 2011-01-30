<?php

/**
 * Maps a jork select to a db select.
 */
abstract class JORK_Mapper_Select {

    /**
     * @var JORK_Query_Select
     */
    protected $_jork_query;

    /**
     * @var DB_Query_Select
     */
    protected $_db_query;

    /**
     * @var array<JORK_Mapper_Result>
     */
    protected $_mappers;

    /**
     * @var JORK_Naming_Service
     */
    protected $_naming_srv;

    /**
     * @var boolean
     */
    public $has_implicit_root;

    /**
     * @var JORK_Mapping_Schema
     */
    protected $_implicit_root;

    protected function  __construct(JORK_Query_Select $jork_query) {
        $this->_jork_query = $jork_query;
        $this->_db_query = new DB_Query_Select;
        $this->_naming_srv = new JORK_Naming_Service;
    }

    public static function for_query(JORK_Query_Select $jork_query) {
        if (count($jork_query->from_list) == 1
                &&  ! array_key_exists('alias', $jork_query->from_list[0])) {
            return new JORK_Mapper_Select_ImplRoot($jork_query);
        } else {
            return new JORK_Mapper_Select_ExplRoot($jork_query);
        }
    }

    public function map() {

        $this->map_from();

        $this->map_join();

        $this->map_with();

        $this->map_select();

        $this->map_where();

        $this->map_group_by();

        $this->map_order_by();

        return array($this->_db_query, $this->_mappers);
    }

    protected function create_entity_mapper($select_item) {
        return new JORK_Mapper_Entity($this->_naming_srv
                , $this->_jork_query
                , $this->_db_query
                , $select_item);
    }

    protected abstract function map_from();

    protected function map_join() {
        
    }

    protected abstract function map_with();

    protected abstract function map_db_expression($expr);

    protected abstract function map_select();

    protected abstract function add_projections(JORK_Query_PropChain $prop_chain, $projections);

    protected abstract function resolve_db_expr(DB_Expression $expr);
    

    /**
     * Maps a binary operator expression to a DB expression. Both operands
     * should be the same class. The class/property names are replaced
     * with the corresponding primary key columns.
     * 
     * @param DB_Expression_Binary $expr
     * @throws JORK_Exception if the two operands are not the same class
     */
    protected function obj2condition(DB_Expression_Binary $expr) {
        if (is_array($expr->left_operand) && $expr->operator == '='
                && is_array($expr->right_operand)) {
            //TODO resolving object equality check to primary key equality checks
            list($left_mapper, $left_ent_schema, $left_last_prop)
                    = $expr->left_operand;
            list($right_mapper, $right_ent_schema, $right_last_prop)
                    = $expr->right_operand;
            if ($left_ent_schema->components[$left_last_prop]['class']
                    != $right_ent_schema->components[$right_last_prop]['class'])
                throw new JORK_Exception("unable to check equality of class '"
                        . $left_ent_schema->components[$left_last_prop]['class'] . "' with class '"
                        . $right_ent_schema->components[$right_last_prop]['class'] . "'");
            //holy shit... it's coming -.-
            $left_prop_chain = array($left_last_prop
                , JORK_Model_Abstract::schema_by_class($left_ent_schema->components[$left_last_prop]['class'])->primary_key());
            $left_mapper->merge_prop_chain($left_prop_chain);
            $expr->left_operand = $left_mapper->resolve_prop_chain($left_prop_chain);

            $right_prop_chain = array($right_last_prop
                , JORK_Model_Abstract::schema_by_class($right_ent_schema->components[$right_last_prop]['class'])->primary_key());
            $right_mapper->merge_prop_chain($right_prop_chain);
            $expr->right_operand= $right_mapper->resolve_prop_chain($right_prop_chain);
        } elseif (is_array($expr->left_operand) || is_array($expr->right_operand))
        //only one operand was an array
            throw new JORK_Exception();
    }

    /**
     * Maps the where clause of the jork query
     *
     * @see JORK_Mapper_Select::$_jork_query
     * @see JORK_Mapper_Select::$_db_query
     * @see JORK_Mapper_Select::resolve_db_expr()
     */
    protected function map_where() {
        foreach ($this->_jork_query->where_conditions as $cond) {
            $this->_db_query->where_conditions []= $this->resolve_db_expr($cond);
        }
    }

    protected abstract function map_group_by();

    protected abstract function map_order_by();

}