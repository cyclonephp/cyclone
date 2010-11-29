<?php


class JORK_Schema {

    public $class;

    public $table;

    public $columns;

    public $components;

    public function primary_key() {
        foreach ($this->columns as $name => $def) {
            if (array_key_exists('primary', $def))
                return $name;
        }
    }
    
}