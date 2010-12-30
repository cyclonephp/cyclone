<?php

class JORK_Mapper_Component_OneToOne extends JORK_Mapper_Component {

    protected function comp2join() {
        $comp_schema = $this->_parent_mapper->_entity_schema->components[$this->_comp_name];
        
        $local_join_col = $comp_schema['join_column'];
        $local_table = $this->_parent_mapper->_entity_schema->table_name_for_column($local_join_col);
        $local_table_alias = $this->_parent_mapper->table_alias($local_table);

        $remote_join_col = array_key_exists('inverse_join_column', $comp_schema)
                ? $comp_schema['inverse_join_column']
                : $this->_entity_schema->primary_key();

        $remote_table = $this->_entity_schema->table_name_for_column($remote_join_col);
        $remote_table_alias = $this->_naming_srv->table_alias($this->_entity_alias, $remote_table);

        $this->_db_query->joins []= array(
            'table' => array($remote_table, $remote_table_alias),
            'type' => 'LEFT',
            'conditions' => array(
                array(
                    $local_table_alias.'.'.$local_join_col
                    , '='
                    , $remote_table_alias.'.'.$remote_join_col
                )
            )
        );

    }

    protected function  comp2join_reverse() {
        
    }

}