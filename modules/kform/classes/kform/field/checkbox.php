<?php


class KForm_Field_Checkbox extends KForm_Field {

    public function  __construct(KForm $form, $name, array $model) {
        parent::__construct($form, $name, $model, 'checkbox');
    }

    /**
     * converts 'on' value to true, eveything else to false
     *
     * @param string $val
     */
    public function set_data($val) {
        $this->value = (boolean) $val == 'on';
    }

    public function pick_input(&$src, &$saved_data = array()) {
        $this->value = (boolean) Arr::get($src, $this->name);
    }
}