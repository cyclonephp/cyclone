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
        $this->_mapper = new JORK_Mapper_Entity($this->_jork_query
                , $this->_alias_factory
                , &$this->_db_query);

        return array($this->_db_query, $this->_mapper);
    }


}