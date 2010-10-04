<?php


class KForm_Input_Checkbox extends KForm_Input {

    public function  __construct($name, array $model) {
        parent::__construct($name, $model, 'checkbox');
    }

    /**
     * converts 'on' value to true, eveything else to false
     *
     * @param string $val
     */
    public function set_val($val) {
        $this->value = (boolean) $val == 'on';
    }

    public function pick_val(&$src, $saved_data = array()) {
        $this->value = (boolean) Arr::get($src, $this->name);
    }
}