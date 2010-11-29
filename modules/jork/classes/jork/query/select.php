<?php


class JORK_Query_Select {

    /**
     * @var array
     */
    public $select_list;

    /**
     * @var array
     */
    public $from_list;

    /**
     * @var array
     */
    public $with_list;

    /**
     * @var array
     */
    public $join_list;

    /**
     * @var array
     */
    public $where_conditions;

    /**
     * @var array
     */
    public $order_by;

    /**
     * @var array
     */
    public $group_by;

    /**
     * @var int
     */
    public $offset;

    /**
     * @var int
     */
    public $limit;

    public function  __construct() {
        $this->join_list = new ArrayObject;
        $this->with_list = new ArrayObject;
    }

    public function select() {
        foreach (func_get_args() as $arg) {
            $this->select_list []= $arg;
        }
    }

    public function from($entity_class_def) {
        $this->from_list []= $entity_class_def;
    }

    public function with($component_path) {
        $this->with_list []= $component_path;
        return $this;
    }

    public function join($entity_class_def, $type = 'INNER') {
        $this->join_list []= array(
            'entity_class_def' => $entity_class_def,
            'type' => $type
        );
        $this->_last_join = $this->join_list[count($this->join_list) - 1];
        return $this;
    }

    public function on() {
        
    }

//    protected function merge_path(array $path, $alias) {
//        $merge_into = $this->with_list;
//        $path_last = count($path) - 1;
//        for ($i = 0; $i <= $path_last; $i++) {
//            $item = $path[$i];
//            $found_existing = false;
//            foreach ($merge_into as &$existing_component) {
//                if ($existing_component['component'] == $item) {
//                    $merge_into = &$existing_component['nexts'];
//                    $found_existing = true;
//                }
//            }
//            if ( ! $found_existing) {
//                $new_item = array(
//                    'component' => $item,
//                    'nexts' => new ArrayObject
//                );
//                if ($i == $path_last) {
//                    $new_item['alias'] = $alias;
//                }
//                $merge_into []= $new_item;
//                $merge_into = &$new_item['nexts'];
//            }
//        }
//    }

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