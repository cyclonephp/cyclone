<?php


class JORK_Query_Select {

    public $entity;

    public $joins;

    public $where_conditions;

    public $order_by;

    public $group_by;

    public $offset;

    public $limit;

    protected $alias_factory;

    public function  __construct() {
        $this->alias_factory = new JORK_Alias_Factory($this);
        $this->joins = new ArrayObject;
    }

    public function join($component_path, $type = 'INNER') {
        list($path, $alias) = JORK_Alias_Factory::entitydef_segments($component_path);
        $this->merge_join_path(explode('.', $path), $alias);
        return $this;
    }

    protected function merge_join_path(array $path, $alias) {
        $merge_into = $this->joins;
        $path_last = count($path) - 1;
        for ($i = 0; $i <= $path_last; $i++) {
            $item = $path[$i];
            $found_existing = false;
            foreach ($merge_into as &$existing_component) {
                if ($existing_component['component'] == $item) {
                    $merge_into = &$existing_component['nexts'];
                    $found_existing = true;
                }
            }
            if ( ! $found_existing) {
                $new_item = array(
                    'component' => $item,
                    'nexts' => new ArrayObject
                );
                if ($i == $path_last) {
                    $new_item['alias'] = $alias;
                }
                $merge_into []= $new_item;
                $merge_into = &$new_item['nexts'];
            }
        }
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