<?php

abstract class Log_Adapter_Abstract implements Log_Adapter {

    protected $_entries = array();

    protected static $_remote_addr;

    protected $_time_format;

    public function  __construct($time_format = 'Y-m-d h:i:s') {
        $this->_time_format = $time_format;
        register_shutdown_function(array($this, 'write_entries'));
        if (NULL === self::$_remote_addr) {
            self::$_remote_addr = isset($_SERVER['REMOTE_ADDR'])
                ? $_SERVER['REMOTE_ADDR']
                : 'unknown';
        }
    }

    public function add_entry($level, $message, $code = NULL) {
        if (Log::$level_order[$level] >= Log::$level_order[Log::$log_level]) {
            $this->_entries []= array(
                'level' => 'level',
                'time' => date($this->_time_format),
                'message' => $message,
                'remote_addr' => self::$_remote_addr,
                'code' => $code
            );
        }
    }

    public function add_debug($message, $code = NULL) {
        $this->add_entry(Log::DEBUG, $message, $code);
    }

    public function add_info($message, $code = NULL) {
        $this->add_entry(Log::INFO, $message, $code);
    }

    public function add_warning($message, $code = NULL) {
        $this->add_entry(Log::WARNING, $message, $code);
    }

    public function add_error($message, $code = NULL) {
        $this->add_entry(Log::ERROR, $message, $code);
    }

}