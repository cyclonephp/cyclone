<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
class DB_Query_Prepared_Select extends DB_Query_Prepared_Abstract {

    public function exec() {
        return $this->_executor->exec_select($this->_prepared_stmt, $this->_params);
    }
    
}
