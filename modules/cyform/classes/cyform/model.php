<?php

/**
 * @author Bence Eros
 * @package CyForm
 */
class CyForm_Model {

    public $result_type = 'array';

    public $theme;

    public $title;

    public $attributes = array(
        'method' => 'post',
        'action' => ''
    );

    public $fields = array();

    public $view = 'form';

    /**
     * @param string $result_type
     * @return CyForm_Model
     */
    public function result($result_type) {
        $this->result_type = $result_type;
        return $this;
    }

    /**
     * @param string $result_type
     * @return CyForm_Model
     */
    public function result_type($result_type) {
        $this->result_type = $result_type;
        return $this;
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

    /**
     * @param string $method
     * @return CyForm_Model
     */
    public function method($method) {
        $this->attributes['method'] = $method;
        return $this;
    }

    /**
     * @param string $action
     * @return CyForm_Model
     */
    public function action($action) {
        $this->attributes['action'] = $action;
        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @return CyForm_Model
     */
    public function attribute($key, $value) {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * @param CyForm_Model_Field $field
     * @return CyForm_Model
     */
    public function field(CyForm_Model_Field $field) {
        if (is_null($field->name)) {
            $this->fields []= $field;
        } else {
            $this->fields[$field->name] = $field;
        }
        return $this;
    }

    /**
     * @param string $view
     * @return CyForm_Model
     */
    public function view($view) {
        $this->view = $view;
        return $this;
    }

}