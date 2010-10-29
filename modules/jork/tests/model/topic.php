<?php

class Model_Topic extends JORK_Model_Abstract {

    public function setup() {
        $this->schema->table = 'topics';
        $this->schema->columns = array(
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
        $this->schema->components = array(
            'categories' => array(
                'class' => 'Model_Category',
                'mapped_by' => 'topics'
            )
        );
    }

    public static function inst() {
        return parent::_inst(__CLASS__);
    }
}