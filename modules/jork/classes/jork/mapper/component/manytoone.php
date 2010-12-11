<?php

class JORK_Mapper_Component_ManyToOne extends JORK_Mapper_Component {

    protected function comp2join() {
//        $this->_db_query->joins []= array(
//            'table' => array($this->_entity_schema->table, $this->_table)
//            , 'type' => 'INNER'
//            , 'conditions' => array(
//                array($this->_parent_mapper->_table.'.'.$this->_parent_mapper->_entity_schema->components[$this->_component]['join_column']
//                , '=', $this->_table.'.'.$this->_entity_schema->primary_key())
//            )
//        );
        $comp_schema = $this->_parent_mapper->_entity_schema->components[$this->_comp_name];
        $remote_schema = JORK_Model_Abstract::schema_by_class($comp_schema['class']);

        $join_col = $comp_schema['join_column'];

        $join_col_schema = $this->_parent_mapper->_entity_schema->get_property_schema($join_col);

        $join_table = array_key_exists('table', $join_col_schema)
                ? $join_col_schema['table']
                : $this->_parent_mapper->_entity_schema->table;

        $join_table_alias = $this->_naming_srv->table_alias($this->_parent_mapper->_entity_alias, $join_table);

        $remote_join_col = array_key_exists('inverse_join_column', $comp_schema)
                ? $comp_schema['inverse_join_column']
                : $remote_schema->primary_key();

        $remote_join_col_schema = $remote_schema->get_property_schema($remote_join_col);

        $remote_join_table = array_key_exists('table', $remote_join_col_schema)
                ? $remote_join_col_schema['table']
                : $remote_schema->table;

        $remote_join_table_alias = $this->_naming_srv->table_alias($this->_entity_alias, $remote_join_table);

        $this->_db_query->joins []= array(
            'table' => array($remote_join_table, $remote_join_table_alias),
            'type' => 'LEFT',
            'conditions' => array(
                array($join_table_alias.'.'.$join_col, '='
                    , $remote_join_table_alias.'.'.$remote_join_col)
            )
        );

        
    }

    protected function  comp2join_reverse() {
        ;
    }

}