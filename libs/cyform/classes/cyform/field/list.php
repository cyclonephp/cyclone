<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyForm
 */
class CyForm_Field_List extends CyForm_Field {

    public function  load_data_source() {
        if ( ! is_null($this->_model->data_source)) {
            $data_source = $this->_model->data_source;

            $result = call_user_func_array($data_source->callback
                    , $data_source->params);

            $val_field = $data_source->val_field;
            $text_field = $data_source->text_field;

            if (empty($result))
                return;

            if (is_array(next($result))) {
                if (NULL === $val_field) {
                    foreach($result as $val => $row) {
                        $this->_model->items[$val] = $row[$text_field];
                    }
                } else {
                    foreach($result as $row) {
                        $this->_model->items[$row[$val_field]] = $row[$text_field];
                    }
                }
            } else {
                if (NULL === $val_field) {
                    foreach($result as $val => $row) {
                        $this->_model->items[$val] = $row->{$text_field};
                    }
                } else {
                    foreach($result as $row) {
                        $this->_model->items[$row->{$val_field}] = $row->{$text_field};
                    }
                }
            }
        }
    }

    protected function before_rendering() {
        $this->_model->errors = $this->validation_errors;

        if ($this->_model->multiple && is_null($this->value)) {
            $this->value = array();
        }
        
        $this->_model->attributes['name'] = $this->_model->name;

        if ($this->_model->multiple) {
            $this->_model->attributes['name'] .= '[]';
            $this->_model->values = $this->value;
        } else {
            $this->_model->attributes['value'] = $this->value;
        }

        if (NULL === $this->_model->view) {
            $this->_model->view = 'select';
        }
        if ($this->_model->view == 'buttons') {
            $this->_model->view = $this->_model->multiple ? 'checkboxlist' : 'radiogroup';
        } elseif ($this->_model->view == 'select' && $this->_model->multiple) {
            $this->_model->attributes['multiple'] = 'multiple';
        } elseif ($this->_model->view == 'select') {
            if ($this->_model->multiple) {
                $this->_model->attributes['multiple'] = 'multiple';
            } else {
                $this->_model->value = $this->value;
            }
        }
    }
    
}
