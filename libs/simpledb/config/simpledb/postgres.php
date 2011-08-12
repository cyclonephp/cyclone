<?php

return array(
    'adapter' => 'postgres',
    'connection' => array(
        'dbname' => 'simpledb',
        'persistent' => TRUE
    ),
    'pk_generator_sequences' => array(
        'users' => 'seq_users'
    )
);