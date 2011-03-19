<?php

return CyForm::model()
    ->field(CyForm::field('text', 'name')
        ->validator('not_empty')
        ->validator('regex', array('/[a-z]*/'))
        ->validator('numeric', array(), ':1: invalid number format')
);

return array(
    'fields' => array(
        'name' => array(
            'label' => 'name',
            'validation' => array(
                'not_empty' => true,
                'regex' => array(
                    'params' => array('/[a-z]*/')
                ),
                'numeric' => array(
                    'error' => ':1: invalid number format'
                ),
                array(
                    'callback' => array('CyForm_Test', 'custom_callback'),
                    'params' => array('asd'),
                    'error' => 'username :1 is not unique'
                ),
                array(
                    'callback' => array('CyForm_Test', 'custom_callback'),
                    'error' => 'username :1 is not unique'
                )
            )
        )
    )
);
