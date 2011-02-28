<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
class DB_Query_Select extends DB_Query implements DB_Expression {

    public $columns;

    public $tables;

    public $joins = array();

    protected $_last_join;

    public $where_conditions;

    public $group_by;

    public $having_conditions;

    public $order_by;

    public $offset;

    public $limit;

    public $for_update;

    public $unions = array();

    public $hints = array();

    public function columns() {
        if (0 == func_num_args()) {
            $this->columns = array(DB::expr('*'));
        } else {
            $this->columns = func_get_args();
        }
        return $this;
    }

    public function columns_arr($columns) {
        $this->columns = empty($columns) ? array(DB::expr('*')) : $columns;
        return $this;
    }

    public function from($table) {
        $this->tables []= $table;
        return $this;
    }

    public function join($table, $join_type = 'INNER') {
        $join = array(
            'table' => $table,
            'type' => $join_type,
            'conditions' => array()
        );
        $this->joins []= &$join;
        $this->_last_join = &$join;
        return $this;
    }

    public function left_join($table) {
        return $this->join($table, 'LEFT');
    }

    public function right_join($table) {
        return $this->join($table, 'RIGHT');
    }

    public function on() {
        $this->_last_join['conditions'] []= DB::create_expr(func_get_args());
        return $this;
    }

    public function where() {
        $this->where_conditions []= DB::create_expr(func_get_args());
        return $this;
    }

    public function order_by($column, $direction = 'ASC') {
        $this->order_by []= array(
            'column' => $column,
            'direction' => $direction
        );
        return $this;
    }

    public function group_by() {
        $this->group_by = func_get_args();
        return $this;
    }

    public function having() {
        $this->having_conditions []= DB::create_expr(func_get_args());
        return $this;
    }

    public function offset($offset) {
        $this->offset = (int) $offset;
        return $this;
    }

    public function limit($limit) {
        $this->limit = (int) $limit;
        return $this;
    }

    public function for_update() {
        $this->for_update = true;
        return $this;
    }

    public function compile($database = 'default') {
        return DB::inst($database)->compile_select($this);
    }

    /**
     *
     * @param string $database
     * @return DB_Query_Result
     */
    public function exec($database = 'default') {
        return DB::inst($database)->exec_select($this);
    }

    public function  compile_expr(DB_Adapter $adapter) {
        return $adapter->compile_select($this);
    }

    public function union(DB_Query_Select $select, $all = TRUE){
        $this->unions[] = array(
            'select' => $select,
            'all' => $all
        );
        return $this;
    }

    public function hint($hint){
        $this->hints[] = $hint;
        return $this;
    }
}
