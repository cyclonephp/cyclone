<?php


class Model_Category extends JORK_Model_Abstract {


    public function setup() {
        $this->schema->table = 'categories';
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
            'topics' => array(
                'class' => 'Model_Topic',
                'type' => JORK::MANY_TO_MANY,
                'join_table' => array(
                    'name' => 'categories_topics',
                    'join_column' => 'category_fk',
                    'inverse_join_column' => 'topic_fk'
                )
            )
        );
    }
}