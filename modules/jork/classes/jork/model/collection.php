<?php

/**
 * Represents a collection of models which are components of an other model,
 * in other words it is used for storing to-many relationships between objects
 * at runtime.
 */
abstract class JORK_Model_Collection extends ArrayObject {

    public static function for_component($owner, $comp_name) {
        $comp_schema = $owner->schema()->components[$comp_name];
        if (array_key_exists('mapped_by', $comp_schema)) {
            $remote_comp_schema = JORK_Model_Abstract::schema_by_class($comp_schema['class'])
                ->components[$comp_schema['mapped_by']];
            if (JORK::MANY_TO_ONE == $remote_comp_schema['type'])
                return new JORK_Model_Collection_Reverse_ManyToOne($owner
                        , $comp_name, $comp_schema);
            elseif (JORK::MANY_TO_MANY == $remote_comp_schema['type'])
                return new JORK_Model_Collection_Reverse_ManyToMany($owner
                        , $comp_name, $comp_schema);
        } else {
            if (JORK::ONE_TO_MANY == $comp_schema['type']) {
                return new JORK_Model_Collection_OneToMany($owner, $comp_name
                        , $comp_schema);
            } elseif (JORK::MANY_TO_MANY == $comp_schema['type']) {
                return new JORK_Model_Collection_ManyToMany($owner, $comp_name
                        , $comp_schema);
            }
        }
        throw new JORK_Exception("internal error: failed to initialize collection for component '$comp_name'");
    }

    /**
     * The owner of the components, the left side of the to-many relationship.
     *
     * @var JORK_Model_Abstract
     */
    protected $_owner;

    /**
     * The name of the component that's value is stored in this collection.
     *
     * @var string
     */
    protected $_comp_name;

    /**
     * The array representing the collection
     *
     * @var array
     */
    protected $_comp_schema;

    /**
     * Used by subclasses.
     *
     * @var string
     */
    protected $_join_column;

    /**
     * Used by subclasses.
     *
     * @var string
     */
    protected $_inverse_join_column;

    /**
     * Used by subclasses.
     *
     * @var string
     */
    protected $_comp_class;

    /**
     * Every item is a two-element array with the following keys:
     * - persistent: TRUE if the current connection-mapping foreign keys have
     * already been saved into the database
     * - value: a model object
     *
     * @var array
     */
    protected $_storage = array();

    /**
     * Stores the entities deleted from the collection, and performs the
     * required database operations when persisting.
     *
     * @var array
     */
    protected $_deleted = array();

    public function  __construct($owner, $comp_name, $comp_schema) {
        $this->_owner = $owner;
        $this->_comp_name = $comp_name;
        $this->_comp_schema = $comp_schema;
    }

    public function  append($value) {
        $this->_do_append($value);
    }

    protected abstract function _do_append($value);

    public function  offsetGet($key) {
        if ( ! array_key_exists($key, $this->_storage))
            throw new JORK_Exception("undefined index $key in component collection '{$this->_comp_name}'");
        return $this->_storage[$key]['value'];
    }

    public function  offsetSet($key, $val) {
        $this->_storage[$key] = array(
            'persistent' => TRUE,
            'value' => $val
        );
    }

    public function  offsetExists($key) {
        return array_key_exists($key, $this->_storage);
    }

    public function  offsetUnset($key) {
        $this->delete_by_pk($key);
    }

    public abstract function delete_by_pk($key);

    public function delete($value) {
        $this->delete_by_pk($value->pk());
    }

    public function  count() {
        return count($this->_storage);
    }

}