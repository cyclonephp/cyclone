<?php

class Log_Adapter_DB extends Log_Adapter_Abstract {

    private $_db_config;

    private $_table_name;

    private $_columns;

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