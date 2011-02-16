<?php


class JORK_Model_Collection_ManyToMany extends JORK_Model_Collection {

    public function delete_by_pk($pk) {
        $this->_deleted[$pk] = $this->_storage[$pk]['value'];
        unset($this->_storage[$pk]);
    }

    public function notify_pk_creation($owner_pk) {
        $this->save();
    }

    public function save() {
        $pk = $this->_owner->pk();
        $db_conn = $this->_owner->schema()->db_conn;
        if ( ! empty($this->_deleted)) {
            $del_stmt = new DB_Query_Delete;
            $del_stmt->table = $this->_comp_schema['join_table']['name'];
            $del_stmt->conditions = array(
                new DB_Expression_Binary($this->_comp_schema['join_table']['join_column']
                        , '=', new DB_Expression_Custom(''))
            );
            foreach ($this->_deleted as $itm_pk => $del_itm) {
                $del_stmt->conditions[0]->right_operand->str = $itm_pk;
                $del_stmt->exec($db_conn);
            }
        }
        if ( ! empty($this->_storage)) {
            $ins_stmt = new DB_Query_Insert;
            $ins_stmt->table = $this->_comp_schema['join_table']['name'];
            $ins_stmt->values = array();
            $local_join_col = $this->_comp_schema['join_table']['join_column'];
            $inverse_join_col = $this->_comp_schema['join_table']['inverse_join_column'];
            foreach ($this->_storage as $itm) {
                if (FALSE == $itm['persistent']) {
                    $ins_stmt->values []= array(
                        $local_join_col => $pk,
                        $inverse_join_col => $itm['value']->pk()
                    );
                }
            }
            $ins_stmt->exec($db_conn);
        }
    }
   
    
}