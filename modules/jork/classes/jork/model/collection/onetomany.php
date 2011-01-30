<?php


class JORK_Model_Collection_OneToMany extends JORK_Model_Collection {

    public function  __construct($owner, $comp_name, $comp_schema) {
        parent::__construct($owner, $comp_name, $comp_schema);
        $this->_join_column = $comp_schema['join_column'];
        $this->_inverse_join_column = array_key_exists('inverse_join_column', $comp_schema)
                ? $comp_schema['inverse_join_column']
                : $owner->schema()->primary_key();
    }

    protected function  _do_append($value) {
        $this->_storage[$value->pk()] = array(
            'persistent' => FALSE,
            'value' => $value
        );
        $value->{$this->_join_column} = $this->_owner->{$this->_inverse_join_column};
    }

    public function  delete_by_pk($pk) {
        $this->_deleted[$pk] = $this->_storage[$pk];
        $this->_deleted[$pk]['value']->{$this->_join_column} = NULL;
        unset($this->_storage[$pk]);
    }
}