<?php

/**
 * @author Bence Eros
 * @package CyForm
 */
class CyForm_Model {

    public $result_type = 'array';

    public $theme;

    public $title;

    public $attributes = array();

    public $fields;

    public function result($result_type) {
        $this->result_type = $result_type;
    }

    public function result_type($result_type) {
        $this->result_type = $result_type;
    }

    public function theme($theme) {
        $this->theme = $theme;
        return $this;
    }

    public function title($title) {
        $this->title = $title;
        return $this;
    }

    public function attributes($attributes) {
        $this->attributes = $attributes;
        return $this;
    }

    public function attribute($key, $value) {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function field(CyForm_Model_Field $field) {
        if (is_null($field->name)) {
            $this->fields []= $field;
        } else {
            $this->fields[$field->name] = $field;
        }
        return $this;
    }
    
}