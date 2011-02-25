<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyForm
 */
class CyForm_Field_Submit extends CyForm_Field {

    public function  __construct(CyForm $form, $name, array $model) {
        parent::__construct($form, $name, $model, 'submit');
    }

    public function set_data($val) {

    }

    public function get_data() {
        return null;
    }

    protected function  before_rendering() {
        if ( ! array_key_exists('attributes', $this->model)) {
            $this->model['attributes'] = array();
        }

        $this->model['attributes']['value'] = $this->model['label'];
        if ( ! array_key_exists('view', $this->model)) {
            $this->model['view'] = 'submit';
        }
    }

}
