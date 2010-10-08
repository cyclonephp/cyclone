<?php


class KForm_Field_Submit extends KForm_Field {

    public function  __construct(KForm $form, $name, array $model) {
        parent::__construct($form, $name, $model, 'submit');
    }

    public function set_val($val) {

    }

    public function get_val() {
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