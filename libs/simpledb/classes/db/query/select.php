<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
class DB_Query_Select implements DB_Query, DB_Expression {

    public $distinct;

    public $columns;

    public $tables;

    public $joins = NULL;

    protected $_last_join;

    /**
     * @var array<DB_Expression>
     */
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
        $args = func_get_args();
        $this->columns_arr($args);
        return $this;
    }

    public function columns_arr($columns) {
        if (empty($columns)) {
            $this->columns = array(DB::expr('*'));
        } else {
            foreach ($columns as $col) {
                $this->columns []= $col;
            }
        }
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
        return DB::compiler($database)->compile_select($this);
    }

    /**
     *
     * @param string $database
     * @return DB_Query_Result
     */
    public function exec($database = 'default') {
        $sql = DB::compiler($database)->compile_select($this);
        return DB::executor($database)->exec_select($sql);
    }

    public function  prepare($database = 'default') {
        $sql = DB::compiler($database)->compile_select($this);
        return new DB_Query_Prepared_Select($sql, $database, $this);
    }

    public function  compile_expr(DB_Compiler $adapter) {
        return '(' . $adapter->compile_select($this) . ')';
    }

    public function  contains_table_name($table_name) {
        $tbl_name_len = strlen($table_name);
        foreach ($this->tables as $tbl) {
            if (is_array($tbl)) {
                $tbl = $tbl[0];
            }
            // if it's a string, then check if it starts with the table name
            if (is_string($tbl) && substr($tbl, 0, $tbl_name_len) == $table_name)
                return TRUE;

            // if it's not a string then it must be a DB_Query_Select
            if ($tbl->contains_table_name($table_name))
                return TRUE;
        }
        foreach ($this->joins as $join) {
            if (is_array($join['table'])) {
                $join_tbl = $join['table'][0];
            } else {
                $join_tbl = $join['table'];
            }
            if (is_string($join_tbl)
                    && substr($join_tbl, 0, $tbl_name_len) == $table_name)
                return TRUE;
            if ($join_tbl->contains_table_name($table_name))
                return TRUE;

            // for joins, we also have to check join conditions
            foreach($join['conditions'] as $join_cond) {
                if ($join_cond->contains_table_name($table_name))
                    return TRUE;
            }
        }
        return FALSE;
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
