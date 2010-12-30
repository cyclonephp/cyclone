<?php

class JORK_Mapper_Component_OneToMany extends JORK_Mapper_Component {

    protected function comp2join() {

        $parent_ent_schema = $this->_parent_mapper->_entity_schema;

        $comp_schema = $parent_ent_schema->components[$this->_comp_name];

        $local_join_col = $comp_schema['join_column'];
        $local_join_table = $this->_entity_schema->table_name_for_column($local_join_col);
        $local_join_table_alias = $this->table_alias($local_join_table);

        

        $remote_join_col = array_key_exists('inverse_join_column', $comp_schema)
                ? $comp_schema['inverse_join_column']
                : $parent_ent_schema->primary_key();
        $remote_join_table = $parent_ent_schema->table_name_for_column($remote_join_col);
        $remote_join_table_alias = $this->_parent_mapper->table_alias($remote_join_table);


        
        $this->_db_query->joins []= array(
            'table' => array($local_join_table, $local_join_table_alias),
            'type' => 'LEFT',
            'conditions' => array(
                array($remote_join_table_alias
                    .'.'.$remote_join_col
                    , '=', $local_join_table_alias.'.'
                        .$local_join_col)
            )
        );
    }

    protected function comp2join_reverse() {
        $parent_ent_schema = $this->_parent_mapper->_entity_schema;

        $local_comp_schema = $parent_ent_schema->components[$this->_comp_name];

        $comp_schema = JORK_Model_Abstract::schema_by_class($local_comp_schema['class'])->components[$local_comp_schema['mapped_by']];

        $local_join_col = array_key_exists('inverse_join_column', $comp_schema)
                ? $comp_schema['inverse_join_column']
                : $parent_ent_schema->primary_key();
        $local_join_table = $this->_entity_schema->table_name_for_column($local_join_col);
        $local_join_table_alias = $this->table_alias($local_join_table);



        $remote_join_col = $comp_schema['join_column'];
        $remote_join_table = $parent_ent_schema->table_name_for_column($remote_join_col);
        $remote_join_table_alias = $this->_parent_mapper->table_alias($remote_join_table);



        $this->_db_query->joins []= array(
            'table' => array($local_join_table, $local_join_table_alias),
            'type' => 'LEFT',
            'conditions' => array(
                array($remote_join_table_alias
                    .'.'.$remote_join_col
                    , '=', $local_join_table_alias.'.'
                        .$local_join_col)
            )
        );
    }

    /**
     * If the FROM clause of the DB query is empty then adds the table
     * of the parent entity (the one which is required for the join / contains
     * the join column) to the FROM clause of the DB query.
     */
    protected function  parent_to_from() {
        $comp_schema = $this->_parent_mapper->_entity_schema->components[$this->_comp_name];
        $join_col = array_key_exists('inverse_join_column', $comp_schema)
                ? $comp_schema['inverse_join_column']
                : $this->_parent_mapper->_entity_schema->primary_key();
        $tbl_name = $this->_parent_mapper->_entity_schema->table_name_for_column($join_col);
        $this->_parent_mapper->add_table($tbl_name);
    }

}