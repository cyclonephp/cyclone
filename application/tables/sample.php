<?php

return array(
    'columns' => array(
        'id' => array(
            'src' => 'id',
            'title' => 'ID',
            'input' => FALSE
        ),
        'name' => array(
            'title' => 'username',
            'input' => array(
                'type' => 'text',
                'validation' => array()
            )
        )
    ),
    'primary_key' => 'id',
    'edit_method' => 'inline',
    'delete' => array(
        'verify' => array(
            'dialog' => array(
                'title' => 'Are you sure you want to delete this user?',
                'options' => array(
                    'yes' => 'Yes',
                    'no' => 'Cancel'
                )
            )
        )
    )
);