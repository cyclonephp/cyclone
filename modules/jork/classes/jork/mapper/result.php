<?php


class JORK_Mapper_Result {

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

    /**
     * Maps the database query result to an object query result.
     *
     * An object query result is an array of entities if the object query
     * has an implicit root entity, otherwise an array of arrays (rows) where
     * every item is something with a type indicated by the select items of
     * the query.
     *
     * @return array
     */
    public function map() {
        $obj_result = array();
        if ($this->_has_implicit_root) {
            echo "database rows: ".count($this->_db_result).PHP_EOL;
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