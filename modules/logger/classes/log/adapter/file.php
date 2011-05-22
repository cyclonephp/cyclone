<?php

/**
 * @addtogroup logger
 *
 * Writes log entries to plain text files. It creates separate subdirectories
 * in its root directory for each year and month, and puts the entries into
 * separate files for each days. For example if the current date is 2011.05.22
 * then the log entries will be written to @code <root-dir>/2011/05/22.php @endcode .
 */
class Log_Adapter_File extends Log_Adapter_Abstract {

    protected $_root_log_path;

    protected $_time_format;

    protected $_entry_format;

    protected $_umask;

    /**
     * Initializes the adapter and registers \c $this->write_entries()
     * as as shutdown function to ensure that the log entries will be written.
     *
     * @param string $root_log_path the root directory of the log files. The parameter should end with DIRECTORY_SEPARATOR
     * @param string $time_format the parameter of \c date() when an entry is added and its creation time is determined.
     * @param string $entry_format a format mask that will be used to format the output for each entries.
     * @param int $umask UNIX file access mask for the created log files and directories
     */
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