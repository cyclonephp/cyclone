<?php


class JORK_Query_Select {

    public $entity;

    public $joins;

    public $where_conditions;

    public function join($component_path, $type = 'INNER') {
        $this->joins []= array(
            'component_path' => $component_path,
            'type' => $type
        );
        return $this;
    }

    public function where() {
        
    }

    
}