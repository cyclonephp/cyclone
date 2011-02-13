<?php

abstract class JORK_Mapping_Schema_Embeddable {

    public $atomics = array();

    public $components = array();

    public function get_property_schema($name) {
        foreach ($this->atomics as $k => $v) {
            if ($k == $name)
                return $v;
        }
        foreach ($this->components as $k => $v) {
            if ($k == $name)
                return $v;
        }
        throw new JORK_Schema_Exception("property '$name' of {$this->class} does not exist");
    }

}