<?php

/**
 * The result of a SELECT statement executed on a postgres database.
 *
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 * @see DB_Adapter_Postgres::exec_select()
 */
class DB_Query_Result_Postgres extends DB_Query_Result {

    private $_res;

    /**
     * @param resource $res The query result returned by pg_query()
     */
    public function  __construct($res) {
        $this->_res = $res;
    }

    /**
     * {@inheritdoc }
     */
    public function  as_array() {
        return pg_fetch_all($this->_res);
    }

    /**
     * Fetches the next result row in the iteration according to the row type
     * set by rows().
     *
     * @see DB_Query_Result::rows()
     */
    public function next() {
        ++$this->_idx;
        if ('array' == $this->_row_type) {
            $this->_current_row = pg_fetch_assoc($this->_res);
        } else {
            $this->_current_row = pg_fetch_object($this->_res, $this->_idx
                    , $this->_row_type);
        }
    }

    /**
     * Seeks to the first row in the result and sets the internal counter to 0.
     */
    public function rewind() {
        $this->_idx = -1;
        pg_result_seek($this->_res, 0);
        $this->next();
    }

    /**
     * Seeks to the row of the result at $offset
     *
     * @param integer $offset
     */
    public function seek($offset) {
        pg_result_seek($this->_res, $offset);
    }

    /**
     * Checks if there is a next row to fetch by next().
     *
     * @see DB_Query_Result_Postgres::next()
     * @return boolean
     */
    public function valid() {
        return $this->_current_row != FALSE;
    }

    /**
     * Returns the number of rows in the result.
     *
     * @return integer
     */
    public function count() {
        return pg_num_rows($this->_res);
    }
    
}
