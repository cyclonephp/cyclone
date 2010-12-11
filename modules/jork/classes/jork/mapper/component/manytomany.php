<?php

class JORK_Mapper_Component_ManyToMany extends JORK_Mapper_Component {

    protected function comp2join() {
//        $comp_def = $this->_parent_mapper->_entity_schema->components[$this->_component];
//        $join_table_alias = $this->_alias_factory->for_table($comp_def['join_table']['name']);
//        $this->_db_query->joins []= array(
//            'table' => array($comp_def['join_table']['name'], $join_table_alias)
//            , 'type' => 'INNER'
//            , 'conditions' => array(
//                array($this->_parent_mapper->_table.'.'.$this->_parent_mapper->_entity_schema->primary_key()
//                    , '=', $join_table_alias.'.'.$comp_def['join_table']['join_column'])
//            )
//        );
//        $this->_db_query->joins []= array(
//            'table' => array($this->_entity_schema->table, $this->_table)
//            , 'type' => 'INNER'
//            , 'conditions' => array(
//                array($join_table_alias.'.'.$comp_def['join_table']['inverse_join_column']
//                    , '=', $this->_table.'.'.$this->_entity_schema->primary_key())
//            )
//        );
    }

    protected function  comp2join_reverse() {
        ;
    }
    
}