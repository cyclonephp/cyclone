<?php

/**
 * @addtogroup logger
 *
 * Logger adapter that uses SimpleDB for logging.
 */
class Log_Adapter_DB extends Log_Adapter_Abstract {

    private $_db_config;

    private $_table_name;

    private $_columns;

    /**
     * Initializes the adapter and registers \c $this->write_entries()
     * as as shutdown function to ensure that the log entries will be written.
     *
     * @param string $table_name the table name where the entries will be inserted
     * @param array $columns key-value pairs where the keys are log entry properties
     *      and the values are database column names (in the \c $table_name table) where
     *      the given property should be written.
     *      Possible keys (entry properties):
     *          - \c message
     *          - \c level
     *          - \c time
     *          - \c remote_addr
     *          - \c code
     * @param string $time_format
     * @param string $db_config
     */
    public function __construct($table_name, $columns
            , $time_format = 'Y-m-d h:i:s', $db_config = 'default') {
        parent::__construct($time_format);
        $this->_db_config = $db_config;
        $this->_table_name = $table_name;
        $this->_columns = $columns;
    }

    public function write_entries() {
        $stmt = DB::insert($this->_table_name);
        $msg_col = $this->_columns['messages'];
        $time_col = $this->_columns['time'];
        foreach ($this->_entries as $entry) {
            $values = array();
            foreach ($this->_columns as $entry_key => $col_name) {
                if (isset($entry[$entry_key])) {
                    $values[$col_name] = $entry[$entry_key];
                }
            }
            $stmt->values($values);
        }
        $stmt->exec($this->_db_config, FALSE);
    }

}