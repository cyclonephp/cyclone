<?php

/**
 * @author crystal
 * 
 * Record_Base is a lightweight implementation of the Active Record design
 * pattern. An instance of an active record class basically represents
 * a row in a database table.
 *
 * All database tables must be managed via a subclass of Record_Base
 * (except those ones which map an N-N relation between other tables).
 *
 * This class gives general functionalities that you possibly want to use
 * for every table, so it protects you from writing trivial queries many times.
 *
 * Any other custom queries - not supported by Record_Base - should be
 * implemented in the methods of the subclasses.
 *
 * In the subclasses you must use this copypaste code to let
 * Record_Base methods works:
 *
 * public function __construct() {
 *		parent::__construct('user');
 *	}
 *
 *	public static function inst() {
 *		return parent::_inst(__CLASS__);
 *	}
 *
 * where in the parent::__construct() call you have to pass the name of the
 * table that your subclass represents.
 *
 * In the following doc comments you will find some methods marked as 'static',
 * but in the header of these methods you can not find the static keyword. For
 * static-like method calls Record_Base uses the singleton design pattern
 * (because polymorphism for static methods does not work correctly in all 
 * versions of the Zend Engine), so these static methods must be called like
 * this:
 *
 * Record_User::inst()->method_name();
 *
 * (where Record_User is a subclass of Record_Base)
 *
 */
class Record_Base {

    /**
     *
     * @var string set by the constructor, holds the name of the table
     * represented by the subclass
     */
    protected $table_name;

    /**
     *
     * @var string set by the constructor, holds the name of the
     * primary key column in the table
     */
    public $pk;

    /**
     *
     * @var array holds the current values of the row represented by the
     * instance
     */
    protected $row = array();

    /**
     *
     * @var array holds data (associated with the record instance) that is
     * required by the application logic or templates but must be not mapped
     * into the database.
     */
    protected $transient_data = array();

    /**
     *
     * @var boolean dirty bit. True if the data in $row is not the same as
     * the row data in the database table, otherwise false.
     */
    protected $dirty = false;

    /**
     *
     * @var boolean if true, then the record is automatically saved into the
     * database before the garbage collector removes the record object.
     */
    protected $auto_save = false;

    /**
     *
     * @var array holds the singleton values returned by Record_Base::_inst()
     */
    private static $_instances = array();

    /**
     *
     * @var array should hold the column definitions for the table, it must be
     * overriden in the subclasses.
     */
    protected $columns = array();

    /**
     *
     * @var sets which Kohana database connection configuration should be used
     * for this record (this way you can work with more than one databases at
     * the same time).
     */
    protected $database_cfg = 'default';

    /**
     * Called by the constructor of the subclass.
     *
     * @param string $table_name the name of the table that the subclass
     * represents
     * @param string $pk the primary key column name
     * @param array $columns alternatively you can pass the column definitions
     * here instead of overriding the $columns property in the subclass. If the
     * primary key is not listed in this array, the the constructor will add it.
     */
    public function __construct($table_name = null, $pk = 'id', $columns = null) {
        if ($table_name != null) {
            $this->table_name = $table_name;
        }
        $this->pk = $pk;
        if ($columns != null) {
            $this->columns = $columns;
        }
        if ( ! array_key_exists($this->pk, $this->columns)) {
            $this->columns[$this->pk] = 'int primary key auto_increment';
        }
    }

    /**
     * called by the "copypaste" inst() method of the subclass, provides a
     * singleton instance for the static-like method calls.
     *
     * @param string the classname of the current subclass
     * @return Record_Base a singleton instance of the current subclass.
     */
    protected static function _inst($class) {
        if (!array_key_exists($class, self::$_instances)) {
            self::$_instances[$class] = new $class();
        }
        return self::$_instances[$class];
    }

    /**
     * if the record is dirty and $auto_save is true then saves the record.
     * Called by the runtime before the object is removed from the memory.
     */
    public function __destruct() {
        if ($this->dirty && $this->auto_save) {
            $this->persist();
        }
    }

    /**
     * static method. Returns a Record object that represents the row definied
     * by the $pk parameter.
     *
     * @param int/mixed $pk the primary key of the row to be fetched.
     * @param mixed $default the return value if record not found
     * @return Record_Base an instance of the current subclass.
     */
    public function get($pk, $default = null) {
        $result = DB::select()->from($this->table_name)->where($this->pk, '=', $pk)
                ->execute($this->database_cfg)->as_array();
        if (count($result) == 0)
            return $default;
        return arr2obj($result[0], get_class($this));
    }

    /**
     * static method. Returns exactly one Record_Base that matches the
     * conditions passed as parameters. Throws Exception if no record found or
     * more than one record found.
     */
    public function get_one() {
        $query = DB::select()->from($this->table_name);
        for ($i = 0; $i < func_num_args(); $i++) {
            $arr = func_get_arg($i);
            if ( ! is_array($arr))
                throw new Exception($arr.' is not an array');
            list($lval, $op, $rval) = $arr;
            $query = $query->where($lval, $op, $rval);
        }
        $result = $query->execute($this->database_cfg)->as_array();
        $count = count($result);
        if ($count > 1)
            throw new Exception(count($result).' found instead of one.');
        return onerow2obj($result, get_class($this));
    }

    public function get_list() {
        $query = DB::select()->from($this->table_name);
        for ($i = 0; $i < func_num_args(); $i++) {
            $arr = func_get_arg($i);
            if ( ! is_array($arr))
                throw new Exception($arr.' is not an array');
           if (count($arr) == 3) {
                list($lval, $op, $rval) = $arr;
                $query = $query->where($lval, $op, $rval);
            } elseif (count($arr) == 2) {
                list($order_column, $direction) = $arr;
                $query = $query->order_by($order_column, $direction);
            } else
                throw new Exception ('additional parameters must be 3-item long where clauses or 2-item long order by clauses');
        }
        $result = $query->execute($this->database_cfg)->as_array();
        return matrix2objarr($result, get_class($this));
    }

    /**
     * static method. Fetches all rows in the table.
     *
     * @return array<Record_Base> the items in the record are instances of the
     * current subclass.
     */
    public function get_all() {
        $result = DB::select()->from($this->table_name)
                ->order_by($this->pk)->execute($this->database_cfg)->as_array();
        return matrix2objarr($result, get_class($this));
    }

    /**
     * static method. Returns a "page" from the table.
     *
     * Optional parameters can be passed to define filtering of the result.
     * These parameters must be 3-item arrays, where the first and third items
     * are SQL expressions and the second is a binary SQL operator to be applied
     * on the expressions.
     *
     * In the "where" clause of the generated SQL query these filters will be
     * joined via "and" operators.
     *
     * If the additional parameters are 2-item arrays, then they are understood
     * as order by clauses (column, direction)
     *
     * @param int $page the page you want to get
     * @param int $page_size the count of rows in a page
     * @return array<Record_Base> the items in the record are instances of the
     * current subclass.
     */
    public function get_page($page, $page_size) {
        $result = DB::select()->from($this->table_name);
        for ($i = 2; $i < func_num_args(); $i++) {
            $arr = func_get_arg($i);
            if ( ! is_array($arr))
                throw new Excepion($arr.' is not an array');
            if (count($arr) == 3) {
                list($lval, $op, $rval) = $arr;
                $result = $result->where($lval, $op, $rval);
            } elseif (count($arr) == 2) {
                list($order_column, $direction) = $arr;
                $result = $result->order_by($order_column, $direction);
            } else {
                $result->select_array($arr);
                //throw new Exception ('additional parameters must be 3-item long where clauses or 2-item long order by clauses');
            }
        }
        $result = $this->paginate($result->order_by($this->pk), $page, $page_size)
                ->execute($this->database_cfg)->as_array();
        return matrix2objarr($result, get_class($this));
    }

    /**
     *
     * @param Database_Query_Builder_Select $query the query you want the
     * pagination add to
     * @param int $page current page
     * @param int $page_size count of rows to be fetched
     * @return Database_Query_Builder_Select
     */
    protected function paginate($query, $page, $page_size) {
        return $query->offset(($page - 1) * $page_size)->limit($page_size);
    }

    /**
     * deletes the row represented by this object from the database table
     */
    public function delete() {
        DB::delete($this->table_name)->where($this->pk, '=', $this->row[$this->pk])
                ->execute($this->database_cfg);
        $this->dirty = false;
    }

    /**
     * static method. Deletes a row from the database table.
     * @param int $pk the primary key of the row to be deleted.
     */
    public function delete_by_pk($pk) {
        DB::delete($this->table_name)->where($this->pk, '=', $pk)
                ->execute($this->database_cfg);
    }

    /**
     * static method.
     *
     * Optional where parameters can be passed here too, see get_page() docs.
     *
     * @return int the count of rows in the table.
     */
    public function count() {
        $query = DB::select(array('count("*")', 'count'))->from($this->table_name);
        for ($i = 0; $i < func_num_args(); $i++) {
            $arr = func_get_arg($i);
            if ( ! is_array($arr))
                throw new Excepion($arr.' is not an array');
            list($lval, $op, $rval) = $arr;
            $query = $query->where($lval, $op, $rval);
        }
        $result = $query->execute($this->database_cfg)->as_array();
        return $result[0]['count'];
    }

    /**
     * Saves the row. If the primary key column has got value in the record,
     * then updates the represented row, else inserts a new row.
     */
    public function persist($type = null) {
        if ($type == 'insert') {
            $this->insert();
        } elseif ($type == 'update') {
            $this->update();
        } elseif (array_key_exists($this->pk, $this->row) && $this->row[$this->pk] != null) {
            $this->update();
        } else {
            $this->insert();
        }
        $this->dirty = false;
    }

    /**
     *
     * @return int/mixed the primary key of the row, regardless the column name.
     */
    public function get_pk() {
        return Arr::get($this->row, $this->pk);
    }

    /**
     * called by persist(), inserts the row into the table.
     */
    private function insert() {
        //unset($this->row[$this->pk]);
        $result = DB::insert($this->table_name, array_keys($this->row))
                ->values($this->row)
                ->execute($this->database_cfg);
        
        if ( ! Arr::get($this->row, $this->pk)) {
            $this->row[$this->pk] = $result[0];
        }
        $this->dirty = false;
    }

    /**
     * called by persist(), updates the row in the table to the current values
     * in $row
     */
    private function update() {
        DB::update($this->table_name)->set($this->row)->where($this->pk, '=', $this->row[$this->pk])
                ->execute($this->database_cfg);
        $this->dirty = false;
    }

    /**
     * sets the value in $row
     */
    public function __set($key, $value) {
        $this->dirty = true;
        if (is_array($value)) {
            $value = new ArrayObject($value);
        }
        if ( ! array_key_exists($key, $this->columns)) {
            $this->transient_data[$key] = $value;
        } else {
            $this->row[$key] = $value;
        }
    }

    /**
     * gets the value from $row
     */
    public function __get($key) {
        if (array_key_exists($key, $this->columns)) {
            $rval = $this->row[$key];
        } else {
            $rval = $this->transient_data[$key];
        }
        return $rval;
    }

    public function __unset($key) {
        if (array_key_exists($key, $this->row)) {
            unset($this->row[$key]);
        }
    }

    /**
     *
     * @return array the schema definition for the represented table,
     * used by Controller_Record to generate database schema from the
     * Record_Base subclasses.
     */
    public function get_schema_definition() {
        return array('table_name' => $this->table_name,
                'columns' => $this->columns,
                'database' => $this->database_cfg);
    }

    /**
     * @return array $row
     */
    public function as_array() {
        return $this->row;
    }

    /**
     * Sets the current row (but does not persist it).
     * 
     * @param array $row
     */
    public function populate(array $row) {
        $this->row = $row;
    }

}