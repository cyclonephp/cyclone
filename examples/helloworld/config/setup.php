<?php

use cyclone\config\reader;

return array(
    'default' => array(
        'readers' => array(new reader\FileEnvReader),
        'writers' => array()
    )
);
