<?php

return array(
    'log_level' => Log::DEBUG,
    'adapters' => array(
        '' => new Log_Adapter_File(APPPATH . 'logs' . DIRECTORY_SEPARATOR)
    )
);
