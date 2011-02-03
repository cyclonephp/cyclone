<?php


class JORK_Model_Collection_ManyToMany extends JORK_Model_Collection {

    protected function  _do_append($value) {
        $this->_storage[$value->pk()] = array(
            'persistent' => FALSE,
            'value' => $value
        );
    }

    public function delete_by_pk($pk) {
        $this->_deleted[$pk] = $this->_storage[$pk];
        unset($this->_storage[$pk]);
    }

    public function  notify_owner_pk_generation($owner_pk) {
        // nothing to do here
    }
   
    
}