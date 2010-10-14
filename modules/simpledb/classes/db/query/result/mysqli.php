<?php

class DB_Query_Result_Mysqli extends DB_Query_Result {

    protected $result;

    protected $current_row;

    public function  __construct(mysqli_result $result) {
        $this->result = $result;
        $this->next();
    }

    public function current() {
        return $this->current_row;
    }

    public function key() {
        if (is_null($this->index_by))
            return $this->result->current_field;
        if ('array' == $this->row_type)
            return $this->current_row[$this->index_by];
        return $this->current_row->{$this->index_by};
    }

    public function next() {
        if ('array' == $this->row_type) {
            $this->current_row = $this->result->fetch_assoc();
        } else {
            $this->current_row = $this->result->fetch_object($this->row_type);
        }
    }

    public function rewind() {
        $this->result->data_seek(0);
        $this->next();
    }

    public function seek($pos) {
        $this->result->data_seek($pos);
    }

    public function valid() {
        return $this->current_row != null;
    }

    public function  count() {
        return $this->result->num_rows;
    }

    public function  __destruct() {
        $this->result->free();
    }

    public function as_array() {
        $rval = array();
        foreach ($this as $k => $v) {
            $rval[$k] = $v;
        }
        return $rval;
    }

    
}