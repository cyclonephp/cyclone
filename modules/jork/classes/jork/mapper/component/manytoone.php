<?php

class JORK_Mapper_Component_ManyToOne extends JORK_Mapper_Component {

    protected function component2join() {
        $this->_db_query->joins []= array(
            'table' => array($this->_entity_schema->table, $this->_table)
            , 'type' => 'INNER'
            , 'conditions' => array(
                array($this->_parent_mapper->_table.'.'.$this->_parent_mapper->_entity_schema->components[$this->_component]['join_column']
                , '=', $this->_table.'.'.$this->_entity_schema->primary_key())
            )
        );
    }

}