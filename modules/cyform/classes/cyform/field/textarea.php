<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyForm
 */
class CyForm_Field_Textarea extends CyForm_Field {

    public function  __construct(CyForm $form, $name, CyForm_Model_Field $model, $cfg) {
        parent::__construct($form, $name, $model, 'textarea', $cfg);
    }

    protected function before_rendering() {
        $this->model['errors'] = $this->validation_errors;
        if ( ! array_key_exists('attributes', $this->model)) {
            $this->model['attributes'] = array();
        }
        $this->model['value'] = $this->value;
        $this->model['attributes']['name'] = $this->name;
        $this->model['name'] = $this->name;
        if ( ! array_key_exists('view', $this->model)) {
            $this->model['view'] = $this->type;
        }
    }
}
