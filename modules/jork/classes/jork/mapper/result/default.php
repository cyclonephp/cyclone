<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package JORK
 */
class JORK_Mapper_Result_Default extends JORK_Mapper_Result {

    /**
     * @var DB_Query_Result
     */
    private $_db_result;

    /**
     * @var boolean
     */
    private $_has_implicit_root;

    /**
     * @var array<JORK_Mapper_Row>
     */
    private $_mappers;

    public function  __construct(DB_Query_Result $db_result, $has_implicit_root
            , $mappers) {
        $this->_db_result = $db_result;
        $this->_has_implicit_root = $has_implicit_root;
        $this->_mappers = $mappers;
    }

    public function map() {
        $obj_result = array();
        if ($this->_has_implicit_root) {
            foreach ($this->_db_result as $row) {
                list($entity, $is_new) = $this->_mappers[NULL]->map_row($row);
                if ($is_new) {
                    $obj_result []= $entity;
                }
            }
        } else {

        }
        return $obj_result;
    }
    
}