<?php


class DB_Query_Result_Postgres extends DB_Query_Result {

    private $_res;

    public function  __construct($res) {
        $this->_res = $res;
    }

    public function  as_array() {
        return pg_fetch_all($this->_res);
    }

    public function next() {
        ++$this->_idx;
        if ('array' == $this->_row_type) {
            $this->_current_row = pg_fetch_assoc($this->_res);
        } else {
            $this->_current_row = pg_fetch_object($this->_res, $this->_idx
                    , $this->_row_type);
        }
    }

    public function rewind() {
        $this->_idx = -1;
        pg_result_seek($this->_res, 0);
        $this->next();
    }

    public function seek($offset) {
        pg_result_seek($this->_res, $offset);
    }

    public function valid() {
        return $this->_current_row != FALSE;
    }

    public function count() {
        return pg_num_rows($this->_res);
    }
    
}