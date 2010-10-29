<?php


class Model_Post extends JORK_Model_Abstract {


    public function setup() {
        $this->_schema->table = 'posts';
        $this->_schema->columns = array(
            'id' => array(
                'type' => 'int',
                'primary' => true,
                'geneneration_strategy' => 'auto'
            ),
            'name' => array(
                'type' => 'string'
            )
        );
        $this->_schema->components = array(
            'author' => array(
                'class' => 'Model_User',
                'mapped_by' => 'posts'
            )
        );
    }

    public static function inst() {
        return parent::_inst(__CLASS__);
    }
}