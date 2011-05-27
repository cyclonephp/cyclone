<?php

class Log_Adapter_Output extends Log_Adapter_Abstract {

    protected $_entry_format;

    public function  __construct($entry_format = 'time [level] | remote_addr | message (code)'
            , $time_format = 'h:i:s') {
        parent::__construct($time_format);
        $this->_entry_format = $entry_format;
    }

    public function  write_entries() {
        foreach ($this->_entries as $entry) {
            echo strtr($this->_entry_format, $entry) . Env::$eol;
        }
    }

}