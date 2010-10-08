<?php

return array(
    'title' => 'complex form example',
    'fields' => array(
        'name' => array(
            'type' => 'text',
            'label' => 'username',
            //'description' => 'required'
        ),
        'password' => array(
            'type' => 'password',
            'label' => 'password'
        ),
        'role' => array(
            'type' => 'list',
            'label' => 'role',
            'view' => 'select',
            'items' => array(
                '0' => 'user',
                '1' => 'admin'
            )
        ),
        'enabled' => array(
            'type' => 'checkbox',
            'label' => 'enabled'
        ),
        'about' => array(
            'type' => 'textarea',
            'label' => 'about'
        ),
        'gender' => array(
            'type' => 'list',
            'label' => 'gender',
            'view' => 'buttons',
            'items' => array(
                'f' => 'female',
                'm' => 'male'
            )
        ),
        'groups' => array(
            'type' => 'list',
            'label' => 'groups',
            'multiple' => true,
            'view' => 'buttons',
            'items' => array(
                '1' => 'group 01',
                '2' => 'group 02',
                '3' => 'group 03'
            )
        ),
        array(
            'type' => 'submit',
            'label' => 'ok'
        )
    )
);