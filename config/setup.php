<?php

use cyclone\config\reader;

return array(
    'default' => array(
        'readers' => array(new reader\FileEnv),
        'writers' => array()
    )
);
