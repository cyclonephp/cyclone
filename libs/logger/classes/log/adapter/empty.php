<?php

/**
 * @addtogroup logger
 *
 * An empty logger that doesn't do anything. All method bodies are empty. It can
 * be used to turn off logging for a namespace.
 */
class Log_Adapter_Empty implements Log_Adapter {

    public function add_debug($message, $code = NULL) {

    }

    public function add_entry($level, $message, $code = NULL) {

    }

    public function add_error($message, $code = NULL) {

    }

    public function add_info($message, $code = NULL) {

    }

    public function add_warning($message, $code = NULL) {

    }

    public function write_entries() {
        
    }

}