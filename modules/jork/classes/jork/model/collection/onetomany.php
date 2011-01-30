<?php


class JORK_Model_Collection_OneToMany extends JORK_Model_Collection {

    /**
     * Stores the entities deleted from the collection, and performs the
     * required database operations when persisting.
     *
     * @var array
     */
    private $_deleted = array();


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

    protected function  _do_unset($value) {
        unset($this->_storage[$value->pk()]);
        $this->_deleted[$value->pk()] = $value;
    }
}