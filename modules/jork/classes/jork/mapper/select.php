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
            //empty select list with implicit root entity
            $tbl_alias = $this->_naming_service->table_alias($this->_implicit_root->class
                    , $this->_implicit_root->table);
            if (empty($this->_jork_query->select_list)) {
                foreach ($this->_implicit_root->columns as $col_name => $col_def) {
                    $this->_db_query->columns []= $tbl_alias.'.'
                            .(array_key_exists('db_column', $col_def) ? $col_def['db_column'] : $col_name);
                }
            } else {
                $this->add_select_items();
            }
        }
    }

    protected function add_select_items() {
        foreach ($this->_jork_query->select_list as $select_item) {
            $prop_chain = $select_item['prop_chain']->as_array();
            $full_chain = $prop_chain;
            $last_item = array_pop($prop_chain);
            foreach ($prop_chain as $prop) {
                
            }
            echo "\n".$select_item['prop_chain']->as_string()."\n";
            $schema = $this->_naming_service->get_schema($select_item['prop_chain']->as_string());
            if (array_key_exists('type', $schema)) { //atomic property
                if ($this->_has_implicit_root) {
                    $root_schema = JORK_Model_Abstract::schema_by_class($this->_jork_query->from_list[0]['class']);
                    $tbl_alias = $this->_naming_service->table_alias($root_schema->class, $root_schema->table);
                    $this->_db_query->columns []= $tbl_alias.'.'
                            .(array_key_exists('db_column', $schema) ? $schema['db_column'] : $last_item);
                }
            } elseif (array_key_exists('class', $schema)) {
                
            } else
                throw new JORK_Schema_Exception("missing key 'class' in {$select_item['prop_chain']}");
        }
    }

}