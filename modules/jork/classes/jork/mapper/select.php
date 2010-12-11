<?php

/**
 * Maps a jork select to a db select.
 */
class JORK_Mapper_Select {

    /**
     * @var JORK_Query_Select
     */
    protected $_jork_query;

    /**
     * @var DB_Query_Select
     */
    protected $_db_query;

    /**
     * @var array
     */
    protected $_mappers;

    /**
     * @var JORK_Naming_Service
     */
    protected $_naming_srv;

    /**
     * @var boolean
     */
    protected $_has_implicit_root;

    /**
     * @var JORK_Mapping_Schema
     */
    protected $_implicit_root;

    public function  __construct(JORK_Query_Select $jork_query) {
        $this->_jork_query = $jork_query;
        $this->_db_query = new DB_Query_Select;
        $this->_naming_srv = new JORK_Naming_Service;
    }

    public function map() {
        if (count($this->_jork_query->from_list) == 1
                &&  ! array_key_exists('alias', $this->_jork_query->from_list[0])) {
            $this->_has_implicit_root = TRUE;
            $impl_root_class = $this->_jork_query->from_list[0]['class'];
            $this->_implicit_root = JORK_Model_Abstract::schema_by_class($impl_root_class);
            $this->_naming_srv->set_implicit_root($impl_root_class);
        }

        $this->map_from();

        $this->map_join();

        $this->map_with();

        $this->map_select();

        return array($this->_db_query, $this->_mappers);
    }

    protected function create_entity_mapper($select_item) {
        return new JORK_Mapper_Entity($this->_naming_srv
                , $this->_jork_query
                , $this->_db_query
                , $select_item);
    }

    protected function map_from() {
        if ($this->_has_implicit_root) {
            $this->_mappers[NULL] = $this->create_entity_mapper(NULL);
        } else {
            foreach ($this->_jork_query->from_list as $from_item) {
                //fail early
                if ( ! array_key_exists('alias', $from_item))
                        throw new JORK_Syntax_Exception('if the query hasn\'t got an
                            implicit root entity, then all explicit root entities must
                            have an alias name');

                $this->_naming_srv->set_alias($from_item['class'], $from_item['alias']);
                $this->_mappers[$from_item['alias']] =
                        $this->create_entity_mapper($from_item['alias']);

            }
        }
    }

    protected function map_join() {
        
    }

    protected function map_with() {
        foreach ($this->_jork_query->with_list as $with_item) {
            if (array_key_exists('alias', $with_item)) {
                $this->_naming_srv->set_alias($with_item['prop_chain'], $with_item['alias']);
            }
            if ($this->_has_implicit_root) {
                $this->_mappers[NULL]->merge_prop_chain($with_item['prop_chain']->as_array());
            } else {
                $prop_chain = $with_item->as_array();
                $root_entity = array_shift($prop_chain);
                if ( ! array_key_exists($root_entity, $this->_mappers))
                    throw new JORK_Syntax_Exception('invalid root entity in WITH clause: '.$root_entity);

                $this->_mappers[$root_entity]->merge_prop_chain($prop_chain);
            }
        }
    }

    protected function map_select() {
        if (empty($this->_jork_query->select_list)) {
            foreach ($this->_mappers as $mapper) {
                $mapper->select_all_atomics();
            }
            return;
        }
        foreach ($this->_jork_query->select_list as $select_item) {
            $prop_chain = $select_item['prop_chain']->as_array();
            if ($this->_has_implicit_root) {
                $this->_mappers[NULL]->merge_prop_chain($prop_chain);
            } else {
                $root_entity = array_shift($prop_chain);
                if ( ! array_key_exists($root_entity, $this->_mappers))
                    throw new JORK_Syntax_Exception('invalid property chain in select clause:'
                            .$select_item['prop_chain']->as_string());
                if (empty($prop_chain)) {
                    $this->_mappers[$root_entity]->select_all_atomics();
                } else {
                    $this->_mappers[$root_entity]->merge_prop_chain($prop_chain);
                }
            }
            if (array_key_exists('projection', $select_item)) {
                $this->add_projections($select_item['prop_chain'], $select_item['projection']);
            }
        }
    }

    protected function add_projections(JORK_Query_PropChain $prop_chain, $projections) {
        
    }

}