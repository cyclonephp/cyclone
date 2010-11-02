<?php


class Model_Category extends JORK_Model_Abstract {


    public function setup() {
        $this->_schema->table = 't_categories';
        $this->_schema->columns = array(
            'id' => array(
                'type' => 'int',
                'primary' => true,
                'geneneration_strategy' => 'auto'
            ),
            'name' => array(
                'type' => 'string',
                'max_length' => 64,
                'not null' => true
            )
        );
        $this->_schema->components = array(
            'topics' => array(
                'class' => 'Model_Topic',
                'type' => JORK::MANY_TO_MANY,
                'mapped_by' => 'categories'
            )
        );
    }

    public static function inst() {
        return parent::_inst(__CLASS__);
    }
    
}
