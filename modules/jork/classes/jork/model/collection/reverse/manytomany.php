<?php


class JORK_Model_Collection_Reverse_ManyToMany extends JORK_Model_Collection {

    public function  __construct($owner, $comp_name, $comp_schema) {
        parent::__construct($owner, $comp_name, $comp_schema);
        $this->_join_column = JORK_Model_Abstract::schema_by_class($comp_schema['class'])
                ->primary_key();
        $this->_inverse_join_column = $owner->schema()->primary_key();
    }

    public function delete_by_pk($pk) {
        $this->_deleted[$pk] = $this->_storage[$pk];
        unset($this->_storage[$pk]);
    }

    public function notify_pk_creation($owner_pk) {
        $this->save();
    }

    public function save() {

    }
    
}