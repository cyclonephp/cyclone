<?php


class JORK_Model_Collection_ManyToMany extends JORK_Model_Collection {

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