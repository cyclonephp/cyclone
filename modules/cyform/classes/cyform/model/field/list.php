<?php

/**
 * @author Bence Eros
 * @package CyForm
 */
class CyForm_Model_Field_List extends CyForm_Model_Field {

    public $type = 'list';

    /**
     *
     * @var CyForm_Model_DataSource
     */
    public $data_source;

    public $items = array();

    public $multiple;

    public function  __construct($name = NULL) {
        $this->name = $name;
    }

    /**
     * @param CyForm_Model_DataSource $data_source
     * @return CyForm_Model_Field_List
     */
    public function source(CyForm_Model_DataSource $data_source) {
        $this->data_source = $data_source;
        return $this;
    }

    /**
     * @throws CyForm_Exception
     */
    public function  type($type) {
        throw new CyForm_Exception('the type attribute of lists is immutable');
    }

    /**
     * @param array $items
     * @return CyForm_Model_Field_List
     */
    public function items($items) {
        $this->items = $items;
        return $this;
    }

    /**
     * @param scalar $val
     * @param string $text
     * @return CyForm_Model_Field_List
     */
    public function item($val, $text) {
        $this->items[$val] = $text;
        return $this;
    }

    /**
     * @param boolean $multiple
     * @return CyForm_Model_Field_List
     */
    public function multiple($multiple = TRUE) {
        $this->multiple = $multiple;
        return $this;
    }

}