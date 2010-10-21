<?php


class Model_User extends JORK_Model_Abstract {


    protected function setup() {
        $this->schema->table = 'users';
        $this->schema->columns = array(
            'id' => array(
                'type' => 'int',
                'primary' => true,
                'geneneration_strategy' => 'auto'
            ),
            'name' => array(
                'type' => 'string',
                'not null' => true,
                'max_length' => 64
            ),
            'password' => array(
                'type' => 'string',
                'not null' => true,
                'length' => 32
            ),
            'created_at' => array(
                'type' => 'datetime',
                'not null' => true
            )
        );
        $this->schema->secondary_tables = array(
            'user_details' => array(
                'email' => array(
                    'type' => 'string',
                    'max_length' => 128
                )
            )
        );
        $this->schema->components = array(
            'posts' => array(
                'class' => 'Model_Post',
                'type' => JORK::ONE_TO_MANY,
                'join_column' => 'user_fk'
            )
        );
    }
    
}