<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyForm
 */
class CyForm_Field_Checkbox extends CyForm_Field {

    public function  __construct(CyForm $form, $name, array $model) {
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
