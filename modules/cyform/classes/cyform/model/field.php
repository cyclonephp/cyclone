<?php

/**
 * @author Bence Eros
 * @package CyForm
 */
class CyForm_Model_Field {

    public $name;

    public $type;

    public $label;

    public $description;

    public $view;

    public $validators;

    public function  __construct($name = NULL) {
        $this->name = $name;
    }

    /**
     *
     * @param string $name
     * @return CyForm_Model_Field
     */
    public function type($type) {
        $this->type = $type;
        return $this;
    }

    /**
     * @param string $label
     * @return CyForm_Model_Field
     */
    public function label($label) {
        $this->label = $label;
        return $this;
    }

    /**
     * @param string $description
     * @return CyForm_Model_Field
     */
    public function description($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @param string $description
     * @return CyForm_Model_Field
     */
    public function descr($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @param string $view
     * @return CyForm_Model_Field
     */
    public function view($view) {
        $this->view = $view;
        return $this;
    }

    /**
     *
     * @param mixed $validator
     * @param mixed $params
     * @param string $error_msg
     * @return CyForm_Model_Field
     */
    public function validator($validator, $params = TRUE, $error_msg) {
        if (is_string($validator)) {
            $this->validators[$validator] = $params;
        } else {
            $this->validators []= array(
                'callback' => $validator,
                'params' => $params,
                'error' => $error_msg
            );
        }
        return $this;
    }
}