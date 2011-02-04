<?php


class JORK_Model_Collection_Reverse_ManyToOne extends JORK_Model_Collection {

    public function  __construct($owner, $comp_name, $comp_schema) {
        parent::__construct($owner, $comp_name, $comp_schema);
        $remote_comp_schema = JORK_Model_Abstract::schema_by_class($comp_schema['class'])
            ->components[$comp_schema['mapped_by']];

        $this->_inverse_join_column = $remote_comp_schema['join_column'];
        $this->_join_column = array_key_exists('inverse_join_column', $remote_comp_schema)
                ? $remote_comp_schema['invserse_join_column']
                : JORK_Model_Abstract::schema_by_class($comp_schema['class'])->primary_key();
    }

    protected function  _do_append($value) {
        $this->_storage[$value->pk()] = array(
            'persistent' => FALSE,
            'value' => $value
        );
        $value->{$this->_inverse_join_column} = $this->_owner->{$this->_join_column};
    }

    public function delete_by_pk($pk) {
        $this->_deleted[$pk] = $this->_storage[$pk];
        $this->_deleted[$pk]['value']->{$this->_inverse_join_column} = NULL;
        unset($this->_storage[$pk]);
    }

    public function  notify_owner_insertion($owner_pk) {
        if (array_key_exists('inverse_join_column', $this->_comp_schema)
                && ($this->_owner->schema()->primary_key()
                != $this->_comp_schema['inverse_join_column'])) {
            //we are not joining on the primary key of the owner
            return;
                }
        $itm_join_col = $this->_inverse_join_column;
        foreach ($this->_storage as $item) {
            $item['persistent'] = FALSE;
            $item['value']->$itm_join_col = $owner_pk;
        }
        $this->save();
    }

    public function save() {
        
    }
    
}