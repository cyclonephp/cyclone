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

    }


    
}