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
    protected $_naming_service;

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
        $this->_naming_service = new JORK_Naming_Service;
    }

    public function map() {

        $this->_has_implicit_root = count($this->_jork_query->from_list) == 1
                &&  ! array_key_exists('alias', $this->_jork_query->from_list[0]);

        if ($this->_has_implicit_root) {
            $this->_implicit_root = JORK_Model_Abstract::schema_by_class($this->_jork_query->from_list[0]['class']);
        }

        $this->map_from();

        $this->map_join();

        $this->map_with();

        $this->map_select();

        return array($this->_db_query, $this->_mappers);
    }

    protected function map_from() {
        if ($this->_has_implicit_root) {
            $from_item = $this->_jork_query->from_list[0];
            $this->_naming_service->set_implicit_root($this->_jork_query->from_list[0]['class']);
            $schema = $this->_naming_service->get_schema($from_item['class']);
            $this->_db_query->tables []= array($schema->table
                    , $this->_naming_service->table_alias($from_item['class'], $schema->table));
        } else {
            foreach ($this->_jork_query->from_list as $from_item) {
                $this->_naming_service->set_alias($from_item['class'], $from_item['alias']);
                $schema = $this->_naming_service->get_schema($from_item['alias']);
                $this->_db_query->tables []= array($schema->table
                    , $this->_naming_service->table_alias($from_item['alias'], $schema->table));
            }
        }
    }

    protected function map_join() {
        
    }

    protected function map_with() {
        
    }

    protected function map_select() {
        if ($this->_has_implicit_root) {
            $this->_mappers[$this->_implicit_root->class] = 
                    new JORK_Mapper_Entity($this);
        } else {
            if ($this->_jork_query->select_list != NULL) {
                foreach ($this->_jork_query->select_list as $select_item) {
                    $entity_mapper = new JORK_Mapper_Entity($this, $select_item);
                    if (array_key_exists('alias', $select_item)) {
                        $key = $select_item['alias'];
                    } else {
                        $key = $select_item['prop_chain']->as_string();
                    }
                    $this->_mappers[$key] = $entity_mapper;
                }
            } else {
                foreach ($this->_jork_query->from_list as $from_item) {
                    if ( !array_key_exists('alias', $from_item))
                        throw new JORK_Syntax_Exception('if the query hasn\'t got an
                            implicit root entity, then all explicit root entities must
                            have an alias name');
                    //mapper should only be addedif it wasn't added previously
                    // eg. by a with clause mapping
                    if ( !array_key_exists($from_item['alias'], $this->_mappers)) {
                        $this->_mappers[$from_item['alias']]
                            = new JORK_Mapper_Entity($this, $from_item['alias']);
                    }
                }
            }
        }
    }

}