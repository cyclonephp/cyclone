<?php


class KForm_Field_List extends KForm_Field {

    public function  __construct($name, array $model) {
        parent::__construct($name, $model, 'radiogroup');
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
    
}