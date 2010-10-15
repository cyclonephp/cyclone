<?php


abstract class Record_Base {

    /**
     *
     * @var array classname => singleton instance pairs
     */
    private static $_instances = array();

    protected $_row = array();

    protected $_transient_data = array();

    protected $_dirty = false;
    
    protected $_auto_save = false;

    protected $_schema;

    protected abstract function setup();

    protected static function _inst($classname) {
        if ( ! array_key_exists($classname, self::$_instances)) {
            $inst = new $classname;
            $inst->_schema = new stdClass;
            $inst->_schema->class = $classname;
            $inst->setup();
            self::$_instances[$classname] = $inst;
        }
        return self::$_instances[$classname];
    }

    protected function schema() {
        if ( ! array_key_exists(get_class($this), self::$_instances)) {
            self::_inst(get_class($this));
        }
        return self::$_instances[get_class($this)]->_schema;
    }

    public function get($id) {
        $query = DB::select()
                ->from($this->schema()->table_name)
                ->where($this->schema()->primary_key, '=', DB::esc($id))
                ->exec($this->schema()->database)
                        ->rows($this->schema()->class)->as_array();
        return $query[0];

    }

    public function get_one() {
        $schema = $this->schema();
        $query = DB::select()->from($schema->table_name);
        $args = func_get_args();
        $this->build_sfw($query, $args);
        $result = $query->exec($schema->database)->rows($schema->class)->as_array();
        if (empty($result))
            return null;
        return $result[0];
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

}