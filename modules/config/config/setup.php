<?php

return array(
    'default' => array(
        'readers' => array(new Config_Reader_File_Env, new Config_Reader_File)
    ),
    'test' => array(
        'readers' => array()
    )
);