<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
abstract class Record_Abstract {

    /**
     *
     * @var array classname => singleton instance pairs
     */
    private static $_instances = array();

    /**
     *
     * @var array holds the data of the database row represented by the object
     */
    protected $_row = array();

    /**
     *
     * @var array holds the transient properties of the object that hasn't got
     * suitable database fields
     */
    protected $_transient_data = array();

    /**
     *
     * @var stdClass this property only has non-null value in the singleton
     * instances. This holds the mapping-related information. Must be set up
     * in the setup() abstract method.
     *
     * @property database
     * @property table_name
     * @property columns
     * @property class
     */
    protected $_schema;

    /**
     * the $_schema object properties must be set up here.
     */
    protected abstract function setup();

    /**
     * This method must be called by the singleton accessor static methods
     * of descendant Record classes. The parameter must be __CLASS__ in all cases.
     * A singleton instance is created for the class, and it's setup() method is
     * called.
     *
     * @param string $classname
     * @return Record_Abstract
     */
    protected static function _inst($classname) {
        if ( ! array_key_exists($classname, self::$_instances)) {
            $inst = new $classname;
            $inst->_schema = new Record_Schema;
            $inst->_schema->class = $classname;
            $inst->_row = null;
            $inst->setup();
            self::$_instances[$classname] = $inst;
        }
        return self::$_instances[$classname];
    }

    /**
     * Accessor for the singleton instance related to the object.
     * 
     * @return stdClass
     */
    public function schema() {
        if ( ! array_key_exists(get_class($this), self::$_instances)) {
            self::_inst(get_class($this));
        }
        return self::$_instances[get_class($this)]->_schema;
    }

    /**
     * Returns a record object that represents the databae row
     * that owns the primary key passed by the $id parameter
     *
     * @param int/mixed $id
     * @return Record_Abstract
     */
    public function get($id) {
        $query = DB::select()
                ->from($this->schema()->table_name)
                ->where($this->schema()->primary_key, '=', DB::esc($id))
                ->exec($this->schema()->database)
                        ->rows($this->schema()->class)->as_array();
        if (empty($query))
            return null;
        return $query[0];

    }

    public function get_one() {
        $schema = $this->schema();
        $query = DB::select()->from($schema->table_name);
        $args = func_get_args();
        $this->build_sfw($query, $args);
        $result = $query->exec($schema->database)->rows($schema->class)->as_array();
        switch(count($result)) {
            case 1: return $result[0];
            case 0: return null;
            default: throw new Exception('more than one results: ' . $query->compile($schema->database));
        }
    }

    public function get_list() {
        $schema = $this->schema();
        $query = DB::select()->from($schema->table_name);
        $args = func_get_args();
        $this->build_sfw($query, $args);
        return $query->exec($schema->database)->rows($schema->class);
    }

    public function get_all() {
        $schema = $this->schema();
        return DB::select()->from($schema->table_name)
                ->exec($schema->database)->rows($schema->class);
    }

    public function get_page($page, $page_size) {
        $schema = $this->schema();
        $query = DB::select()->from($schema->table_name);
        $args = func_get_args();
        array_shift($args);
        array_shift($args);
        $this->build_sfw($query, $args);
        $this->paginate($page, $page_size, $query);
        return $query->exec($schema->database)->rows($schema->class);
    }

    protected function paginate($page, $page_size, DB_Query_Select $query) {
        $query->offset(($page - 1) * $page_size)->limit($page_size);
    }

    protected function build_sfw(DB_Query_Select $query, $args) {
        foreach ($args as $arg) {
            if ( ! is_array($arg))
                throw new Exception("$arg is not an array");
            switch (count($arg)) {
                case 2: $query->order_by($arg[0], $arg[1]); break;
                case 3: $query->where($arg[0], $arg[1], $arg[2]); break;
                default: throw new Exception('arguments must be 2 or 3 length arrays and not '.  count($arg));
            }
        }
    }

    public function delete() {
        $schema = $this->schema();
        switch (func_num_args()) {
            case 0:
                if (is_null($this->_row))
                    throw new Exception('static singleton instances can not be deleted');
                return DB::delete($schema->table_name)->where($schema->primary_key, '=', DB::esc($this->_row[$schema->primary_key]))
                        ->exec($schema->database);
                break;
            case 1:
                $id = func_get_arg(0);
                return DB::delete($schema->table_name)->where($schema->primary_key, '=', DB::esc($id))
                    ->exec($schema->database);
                break;
            default:
                throw new Exception('delete() method can be called at most with 1 parameter');
        }
    }

    public function count() {
        $schema = $this->schema();
        $query = DB::select(array(DB::expr('count(1)'), 'count'))->from($schema->table_name);
        $args = func_get_args();
        $this->build_sfw($query, $args);
        $result = $query->exec($schema->database)->as_array();
        return $result[0]['count'];
    }

    public function save() {
        $schema = $this->schema();
        if (array_key_exists($schema->primary_key, $this->_row)) {
            $this->update();
        } else {
            $this->insert();
        }
    }

    public function insert() {
        $schema = $this->schema();
        $this->id = DB::insert($schema->table_name)->values($this->_row)
                ->exec($schema->database);
    }

    public function update() {
        $schema = $this->schema();
        DB::update($schema->table_name)->values($this->_row)
                ->where($schema->primary_key, '=', DB::esc($this->_row[$schema->primary_key]))
                ->exec($schema->database);
    }

    public function  __get($name) {
        if (array_key_exists($name, $this->schema()->columns)) {
            return Arr::get($this->_row, $name);
        } elseif (array_key_exists($name, $this->_transient_data)) {
            return $this->_transient_data[$name];
        }
        throw new Exception('trying to read non-existent property: '.$name);
    }

    public function  __set($name, $value) {
        if (array_key_exists($name, $this->schema()->columns)) {
            $this->_row[$name] = $value;
        } else {
            $this->_transient_data[$name] = $value;
        }
    }

    public function  __unset($name) {
        if (array_key_exists($name, $this->_row)) {
            unset($this->_row[$name]);
        } elseif (array_key_exists($name, $this->_transient_data)) {
            unset($this->_transient_data[$name]);
        }
    }

    public function  __isset($name) {
        return array_key_exists($name, $this->_row)
                || array_key_exists($name, $this->_transient_data);
    }

    public function as_array() {
        return $this->_row;
    }
    
}
