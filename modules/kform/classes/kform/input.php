<?php


class KForm_Input {

    /**
     *
     * @var array the field model defined in the form definition
     */
    public $model;

    public $name;

    public $type;

    public $value;

    /**
     *
     * @param string $name the name of the input field
     * @param array $model the field definition
     * @param string $type the type of the HTML input
     */
    public function  __construct($name, array $model, $type) {
        $this->model = $model;
        $this->name = $name;
        $this->type = $type;
    }

    public function load_data_source() {
        
    }

    public function set_val($val) {
        $this->value = $val;
    }

    public function get_val() {
        return $this->value;
    }

    

}