<?php

interface Log_Adapter {

    public function add_entry($level, $message, $code = NULL);

    public function add_debug($message, $code = NULL);

    public function add_info($message, $code = NULL);

    public function add_warning($message, $code = NULL);

    public function add_error($message, $code = NULL);

    public function write_entries();

}