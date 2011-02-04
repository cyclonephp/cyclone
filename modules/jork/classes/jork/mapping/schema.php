<?php


class JORK_Mapping_Schema {

    public $db_conn = 'default';

    public $class;

    public $table;

    public $secondary_tables;

    public $columns;

    public $components;

    public function primary_key() {
        foreach ($this->columns as $name => $def) {
            if (array_key_exists('primary', $def))
                return $name;
        }
    }

    public function get_property_schema($name) {
        foreach ($this->columns as $k => $v) {
            if ($k == $name)
                return $v;
        }
        foreach ($this->components as $k => $v) {
            if ($k == $name)
                return $v;
        }
        throw new JORK_Schema_Exception("property '$name' of {$this->class} does not exist");
    }

    public function table_name_for_column($col_name) {
        return array_key_exists('table', $this->columns[$col_name])
                ? $this->columns[$col_name]
                : $this->table;
    }

    public function is_to_many_component($comp_name) {
        if ( ! array_key_exists('mapped_by', ($comp_schema = $this->components[$comp_name])))
            return $comp_schema['type'] == JORK::ONE_TO_MANY
                || $comp_schema['type'] == JORK::MANY_TO_MANY;

        $remote_comp_schema = JORK_Model_Abstract::schema_by_class($comp_schema['class'])
            ->components[$comp_schema['mapped_by']];

        return $remote_comp_schema['type'] == JORK::MANY_TO_MANY
            || $remote_comp_schema['type'] == JORK::MANY_TO_ONE;
    }
    
}