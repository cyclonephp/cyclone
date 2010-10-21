<?php


class Model_Post extends JORK_Model_Abstract {


    public function setup() {
        $this->schema->table = 'posts';
        $this->schema->columns = array(
            'id' => array(
                'type' => 'int',
                'primary' => true,
                'geneneration_strategy' => 'auto'
            ),
            'name' => array(
                'type' => 'string'
            )
        );
        $this->schema->components = array(
            'author' => array(
                'class' => 'Model_User',
                'mapped_by' => 'posts'
            )
        );
    }
}