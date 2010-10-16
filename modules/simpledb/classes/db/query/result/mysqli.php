<?php

class DB_Query_Result_Mysqli extends DB_Query_Result {

    protected $result;

    protected $current_row;

    protected $idx = -1;

    public function  __construct(mysqli_result $result) {
        $this->result = $result;
        $this->next();
    }

    public function current() {
        return $this->current_row;
    }

    public function key() {
        if (is_null($this->index_by)) {
            return $this->idx;
        }
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
        $this->idx++;
    }

    public function rewind() {
        $this->result->data_seek(0);
        $this->idx = -1;
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
            //echo "dumping $k => "; print_r($v);
            $rval[$k] = $v;
        }
        return $rval;
    }

    
}