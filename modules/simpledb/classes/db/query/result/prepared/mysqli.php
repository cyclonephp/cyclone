<?php

class DB_Query_Result_Prepared_MySQLi extends DB_Query_Result {

    private $_stmt;

    private $_is_valid;

    public function  __construct(MySQLI_Stmt $stmt, DB_Query_Select $orig_query) {
        $this->_stmt = $stmt;
        $this->detect_columns($orig_query);
    }

    private function detect_columns(DB_Query_Select $query) {
        $dummy_vals = array();
        foreach ($query->columns as $col) {
            if (is_array($col)) { //alias name
                $col_name = $col[1];
            } else {
                $col_name = $col;
                if ($col_name instanceof DB_Expression_Custom)
                    throw new DB_Exception('failed to determine the count of columns in prepared statement result - please avoid using stars in the SELECT clause');
            }
            $dummy_vals []= NULL;
            $this->_current_row [$col_name]= &$dummy_vals[count($dummy_vals) - 1];
        }

        try {
            call_user_func_array(array($this->_stmt, 'bind_result'), $this->_current_row);
        } catch (ErrorException $ex) {
            throw new DB_Exception('failed to determine the count of columns in prepared statement result - please avoid using stars in the SELECT clause', $ex->getCode(), $ex);
        }
    }

    public function current() {
        return $this->_current_row;
    }

    public function key() {
         if (is_null($this->_index_by)) {
            return $this->_idx;
        }
        if ('array' == $this->_row_type)
            return $this->_current_row[$this->_index_by];
        throw new Exception('cannot fetch prepared statement result into '
                . $this->_row_type.'. Not yet implemented');
    }

    public function next() {
        $this->_is_valid = $this->_stmt->fetch();
        ++$this->_idx;
    }

    public function rewind() {
        $this->_stmt->data_seek(0);
        $this->_idx = -1;
        $this->next();
    }

    public function seek($pos) {
        $this->_stmt->data_seek($pos);
        $this->_idx = $pos;
    }

    public function valid() {
        return $this->_is_valid;
    }

    public function count() {
        return $this->_stmt->num_rows;
    }

    public function __destruct() {
        $this->_stmt->free_result();
    }

    public function as_array() {
        $rval = array();
        foreach ($this as $k => $v) {
            $rval[$k] = $v;
        }
        return $rval;
    }
    
}