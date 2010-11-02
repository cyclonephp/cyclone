<?php


class JORK_Query_Select {

    public $entity;

    public $joins = array();

    public $where_conditions;

    public $order_by;

    public $group_by;

    public $offset;

    public $limit;

    protected $alias_factory;

    public function  __construct() {
        $this->alias_factory = new JORK_Alias_Factory($this);
    }

    public function join($component_path, $type = 'INNER') {
        $this->joins []= array(
            'component_path' => $component_path,
            'type' => $type
        );
        return $this;
    }

    public function where() {
        $this->where_conditions []= JORK::create_expr(func_get_args());
        return $this;
    }

    public function group_by() {
        $this->group_by = func_get_args();
        return $this;
    }

    public function order_by($column, $direction = 'ASC') {
        $this->order_by []= array(
            'column' => $column,
            'direction' => $direction
        );
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

    public function exec($adapter = 'default') {
        JORK::adapter($adapter)->exec_select($this);
    }

    
}