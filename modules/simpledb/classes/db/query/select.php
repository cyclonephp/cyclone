<?php


class DB_Query_Select {

    public $columns;

    public $tables;

    public $joins = array();

    protected $_last_join;

    public $where_conditions;

    public $group_by;

    public $having_conditions;

    public $offset;

    public $limit;

    public $for_update;

    public function columns() {
        if (0 == func_num_args()) {
            $this->columns = array('*');
        } else {
            $this->columns = func_get_args();
        }
        return $this;
    }

    public function columns_arr($columns) {
        $this->columns = empty($columns) ? array('*') : $columns;
        return $this;
    }

    public function from($table) {
        $this->tables []= $table;
        return $this;
    }

    public function join($table, $join_type = 'INNER') {
        $join = array(
            'table' => $table,
            'join_type' => $join_type,
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

    public function group_by() {
        $this->group_by = func_get_args();
        return $this;
    }

    public function having() {
        $this->having_conditions []= DB::create_expr(func_get_args());
        return $this;
    }

    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }

    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function for_update() {
        $this->for_update = true;
        return $this;
    }

    public function compile($database = 'default') {
        return DB::inst($database)->compile_select($this);
    }

    public function exec($database = 'default') {
        return DB::inst($database)->exec_select($this);
    }

    
}
