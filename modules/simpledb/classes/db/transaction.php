<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
class DB_Transaction extends ArrayObject {

    protected $queries;

    public function  __construct($queries = array()) {
        $this->queries = $queries;
    }

    public function append($value) {
        $this->queries []= $value;
    }

    public function  count() {
        return count($this->queries);
    }

    public function  offsetExists($index) {
        return array_key_exists($index, $this->queries);
    }

    public function  offsetGet($index) {
        return $this->queries[$index];
    }

    public function  offsetSet($index, $newval) {
        $this->queries[$index] = $newval;
    }

    public function  offsetUnset($index) {
        unset($this->queries[$index]);
    }

    public function  getIterator() {
        return new ArrayIterator($this->queries);
    }

    public function exec($database = 'default') {
        $db = DB::connector($database);
        $db->autocommit(false);
        foreach ($this->queries as $query) {
            try {
                $query->exec($database);
            } catch (Exception $ex) {
                $db->rollback();
                throw new DB_Exception('failed to execute transaction'
                        , $ex->getCode());
            }
        }
        $db->commit();
        $db->autocommit(true);
    }

}
