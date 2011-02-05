<?php


class Model_ModInfo extends JORK_Mapping_Schema_Embeddable {

    public $atomics = array(
        'created_at' => array(
            'type' => 'datetime',
            'constraints' => array(
                'not null' => true
            )
        ),
        'creator_fk' => array(
            'type' => 'int',
            'constraints' => array(
                'not null' => true
            )
        ),
        'modified_at' => array(
            'type' => 'datetime'
        ),
        'modifier_fk' => array(
            'type' => 'int',
            'constraints' => array(
                'not null' => true
            )
        )
    );
    public $components = array(
        'creator' => array(
            'class' => 'Model_User',
            'type' => JORK::MANY_TO_ONE,
            'join_column' => 'creator_fk'
        ),
        'modifier' => array(
            'class' => 'Model_User',
            'type' => JORK::MANY_TO_ONE,
            'join_column' => 'modifier_fk'
        )
    );

}