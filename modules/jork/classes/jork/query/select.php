<?php


class JORK_Query_Select {

    /**
     * holds the select list of the query. 
     *
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

    /**
     * builder method for the select clause of the query
     *
     * @var string select item
     * @var ...
     * @return JORK_Query_Builder_Select
     */
    public function select() {
        static $pattern = '/^(?<prop_chain>[a-zA-z\.]+)(\{(?<projection>[a-z,]+)\})?( +(?<alias>[a-zA-Z_]+))?$/';
        foreach (func_get_args() as $arg) {
            if ($arg instanceof DB_Expression) {
                $this->select_list []= array(
                    'expr' => $arg->str
                );
                continue;
            }
            preg_match($pattern, $arg, $matches);
            if (empty($matches))
                throw new JORK_Syntax_Exception('invalid select list item: '.$arg);
            $select_item = array(
                'prop_chain' => JORK_Query_PropChain::from_string($matches['prop_chain']),
            );
            if (array_key_exists('projection', $matches)) {
                $select_item['projection'] = explode(',', $matches['projection']);
            }
            if (array_key_exists('alias', $matches)) {
                $select_item['alias'] = $matches['alias'];
            }
            $this->select_list []= $select_item;
        }
        return $this;
    }

    /**
     * Builder method for the from clause if the query.
     *
     * @param string from list item
     * @param ...
     * @return JORK_Query_Builder_Select
     */
    public function from() {
        foreach (func_get_args() as $arg) {
            preg_match('/^(?<class>[a-zA-Z_0-9]+)( +(?<alias>[a-zA-Z_0-9]+))?$/', $arg, $matches);
            if (empty($matches))
                throw new JORK_Syntax_Exception ('invalid from list item: '.$arg);
            $item = array(
                'class' => $matches['class']
            );
            if (array_key_exists('alias', $matches)) {
                $item['alias'] = $matches['alias'];
            }
            $this->from_list []= $item;
        }
        return $this;
    }

    /**
     *
     * @return JORK_Query_Builder_Select
     */
    public function with() {
        foreach (func_get_args() as $arg) {
            if ($arg instanceof JORK_Query_Select) {
                $this->with_list []= $arg;
                continue;
            }
            preg_match('/^(?<prop_chain>[a-zA-Z_0-9.]+)( +(?<alias>[a-zA-Z_0-9]+))?$/', $arg, $matches);
            if (empty($matches))
                throw new JORK_Syntax_Exception ('invalid with list item: '.$arg);
            $item = array(
                'prop_chain' => JORK_Query_PropChain::from_string($matches['prop_chain'])
            );
            if (array_key_exists('alias', $matches)) {
                $item['alias'] = $matches['alias'];
            }
            $this->with_list []= $item;
        }
        return $this;
    }

    public function join($entity_class_def, $type = 'INNER') {
        preg_match('/^(?<class>[a-zA-Z_0-9.]+)( +(?<alias>[a-zA-Z_0-9]+))?$/', $entity_class_def, $matches);
        if (empty($matches))
            throw new JORK_Syntax_Exception('invalid from list item: '.$entity_class_def);
        
        $item = array(
            'type' => $type,
            'class' => $matches['class']
        );
        if (array_key_exists('alias', $matches)) {
            $item['alias'] = $matches['alias'];
        }
        $this->join_list []= $item;
        $this->_last_join = &$this->join_list[count($this->join_list) - 1];
        return $this;
    }

    public function left_join($entity_class_def) {
        $this->join($entity_class_def, 'LEFT');
        return $this;
    }

    public function on() {
        $this->_last_join['condition'] = func_get_args();
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
        $this->where_conditions []= func_get_args();
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