<?php

return array(
    'default' => array(
        'readers' => array(new Config_Reader_File_Env, new Config_Reader_Database('beall', 'megnev', 'ertek', 'default', 'app')),
        'writers' => array(new Config_Writer_Database('beall', 'megnev', 'ertek', 'default', 'app'))
    ),
    'test' => array(
        'readers' => array()
    )
);