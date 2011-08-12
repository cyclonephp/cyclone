<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyForm
 */
class CyForm_Field_Submit extends CyForm_Field {

    public function  __construct(CyForm $form, $name, CyForm_Model_Field $model, $cfg) {
        parent::__construct($form, $name, $model, 'submit', $cfg);
    }

    public function set_data($val) {

    }

    public function get_data() {
        return null;
    }

    protected function  before_rendering() {
        
        if ( ! is_null($this->_model->name)) {
            $this->_model->attributes['name'] = $this->_model->name;
        }
        $this->_model->attributes['value'] = $this->_model->label;
        if (NULL === $this->_model->view) {
            $this->_model->view = 'submit';
        }
    }

}
