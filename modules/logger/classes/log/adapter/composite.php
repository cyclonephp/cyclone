<?php

class Log_Adapter_Composite extends Log_Adapter_Abstract {

    public static function factory() {
        return new Log_Adapter_Composite;
    }

    private $_adapters;

    /**
     * Add the adapter to the adapter list
     * 
     * @param Log_Adapter $adapter
     * @return Log_Adapter_Composite
     */
    public function add(Log_Adapter $adapter) {
        $this->_adapters []= $adapter;
        return $this;
    }

    public function add_entry($level, $message, $code = NULL) {
        foreach ($this->_adapters as $adapter) {
            $adapter->add_entry($level, $message, $code);
        }
    }

    public function  __construct() {
        // just overriding the constructor - nothing to do in this case
    }

    public function write_entries() {
        // the adapters should take care about writing their log messages
        // we do nothing here to prevent duplicate log entries
    }

}