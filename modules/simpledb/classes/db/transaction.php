<?php

/**
 * Represents a list of SQL queries that should be executed in a transaction.
 *
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
class DB_Transaction extends ArrayObject {

    protected $_queries;

    /**
     * @param array $queries the queries to be executed. Further queries added
     * with append() or offsetSet() will be appended to this array.
     */
    public function  __construct($queries = array()) {
        $this->_queries = $queries;
    }

    public function append($value) {
        $this->_queries []= $value;
    }

    public function  count() {
        return count($this->_queries);
    }

    public function  offsetExists($index) {
        return array_key_exists($index, $this->_queries);
    }

    public function  offsetGet($index) {
        return $this->_queries[$index];
    }

    public function  offsetSet($index, $newval) {
        $this->_queries[$index] = $newval;
    }

    public function  offsetUnset($index) {
        unset($this->_queries[$index]);
    }

    public function  getIterator() {
        return new ArrayIterator($this->_queries);
    }

    /**
     * Executes the transaction on the given database.
     *
     * Executes the queries in the same order as they were added to the
     * transaction. If any of the queries throw an exception then rolls
     * back the query then throws a new DB_Exepction thats source is the original
     * exception.
     *
     * If all queries are successfully executed then commits the transaction.
     *
     * @param string $database
     * @throws DB_Exception
     */
    public function exec($database = 'default') {
        $db = DB::connector($database);
        $db->autocommit(false);
        foreach ($this->_queries as $query) {
            try {
                $query->exec($database);
            } catch (Exception $ex) {
                $db->rollback();
                throw new DB_Exception('failed to execute transaction'
                        , $ex->getCode(), $ex);
            }
        }
        $db->commit();
        $db->autocommit(true);
    }

}
