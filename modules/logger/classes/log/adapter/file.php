<?php

class Log_Adapter_File extends Log_Adapter_Abstract {

    protected $_root_log_path;

    protected $_time_format;

    protected $_entry_format;

    protected $_umask;

    public function  __construct($root_log_path
            , $time_format = 'h:i:s'
            , $entry_format = 'time [level] | remote_addr | message (code)'
            , $umask = 0777) {
        parent::__construct($time_format);
        $this->_root_log_path = $root_log_path;
        $this->_entry_format = $entry_format;
        $this->_umask = $umask;
    }

    public function write_entries() {
        $directory = $this->_root_log_path
                . date('Y')
                . DIRECTORY_SEPARATOR
                . date('m');
        if ( ! file_exists($directory)) {
            mkdir($directory, $this->_umask, true);
        }

        $file = $directory . DIRECTORY_SEPARATOR . date('d') . EXT;
        if ( !file_exists($file)) {
            file_put_contents($file, Kohana::FILE_SECURITY.' ?>'.PHP_EOL);
            chmod($file, $this->_umask);
        }

        $entries_txt = array();

        foreach($this->_entries as $entry) {
            $entries_txt []= strtr($this->_entry_format, $entry) . PHP_EOL;
        }

        file_put_contents($file, $entries_txt, FILE_APPEND);
    }
    
}