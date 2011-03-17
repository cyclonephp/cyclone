<?php

/**
 * @author Bence Eros
 * @package CyForm
 */
class CyForm_Model_DataSource {

    public $callback;

    public $val_field;

    public $text_field;

    public function  __construct($callback = NULL) {
        $this->callback = $callback;
    }

    public function val($val_field) {
        $this->val_field = $val_field;
        return $this;
    }

    public function text($text_field) {
        $this->text_field = $text_field;
        return $this;
    }

}