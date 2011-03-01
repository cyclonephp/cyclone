<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyForm
 */
class CyForm_Field_List extends CyForm_Field {

    public function  __construct(CyForm $form, $name, array $model) {
        parent::__construct($form, $name, $model, 'list');
    }

    public function  load_data_source() {
        if (array_key_exists('data_source', $this->model)) {
            $data_source = $this->model['data_source'];
            $params = Arr::get($data_source, 'params', array());
            $result = call_user_func_array($data_source['callback'], $params);

            if ( ! array_key_exists('items', $this->model)) {
                $this->model['items'] = array();
            }

            $val_field = $data_source['val_field'];
            $text_field = $data_source['text_field'];

            if (Arr::get($data_source, 'result', 'array') == 'array') {
                foreach($result as $row) {
                   $this->model['items'] [$row[$val_field]] = $row[$text_field];
                }
            } else {
                foreach($result as $row) {
                   $this->model['items'] [$row->{$val_field}] = $row->{$text_field};
                }
            }
        }
    }

    protected function before_rendering() {
        $this->model['errors'] = $this->validation_errors;
        if ( ! array_key_exists('attributes', $this->model)) {
            $this->model['attributes'] = array();
        }

        $multiple = Arr::get($this->model, 'multiple');

        if ($multiple && is_null($this->value)) {
            $this->value = array();
        }
        $this->model['attributes']['value'] = $this->value;
        $this->model['attributes']['name'] = $this->name;

        if ($multiple) {
            $this->model['attributes']['name'] .= '[]';
        }
        $this->model['attributes']['type'] = $this->type;
        $this->model['name'] = $this->name;

        if ( ! array_key_exists('view', $this->model)) {
            $this->model['view'] = 'select';
        }
        if ($this->model['view'] == 'buttons') {
            $this->model['view'] = $multiple ? 'checkboxlist' : 'radiogroup';
        } elseif ($this->model['view'] == 'select' && $multiple) {
            $this->model['attributes']['multiple'] = 'multiple';
        }
    }
    
}
