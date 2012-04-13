<?php

namespace cyclone;

/**
 * A <code>ValidationAggregate</code> can be used to handle the validation
 * of multiple values simply. The data to be validated should be passed in an
 * associative array. For each value (or field) in the array a @c Validation
 * instance will be created internally. The methods of <code>ValidationAggregate<code>
 * are wrappers (or "short-cuts") to access the properties of the internal validators.
 *
 * @author Bence Eros <crystal@cyclonephp.org>
 * @package cyclone
 * @property-read array<Validator> $validators
 * @property-read boolean $fail_on_first
 */
class ValidationAggregate {

    /**
     * Static factory method
     *
     * @param array $data the data to be validated
     * @param boolean $fail_on_first the default value of @c Validation::$fail_on_first
     *   for each field validator
     * @return ValidationAggregate
     */
    public static function factory($data = NULL, $fail_on_first = FALSE) {
        return new ValidationAggregate($data, $fail_on_first);
    }

    /**
     * @var array
     */
    protected $_data;

    /**
     * The default value of $fail_on_first of
     *
     * @var boolean
     */
    protected $_fail_on_first;

    /**
     * The list of field validators (array keys are field names).
     *
     * @var array<Validator>
     */
    private $_validators = array();

    public function  __construct($data = NULL, $fail_on_first = FALSE) {
        $this->_data = $data;
        $this->_fail_on_first = $fail_on_first;
    }

    /**
     * Sets the data to be validated.
     *
     * @param array $data
     * @throws \cyclone\Exception if <code>$data</code> is not an array and it doesn't
     *  implement <code>ArrayAccess</code>
     * @return ValidationAggregate <code>$this</code>
     */
    public function data($data) {
        if ( ! (is_array($data) || $data instanceof \ArrayAccess))
            throw new Exception("the data to be validated must be an array");

        $this->_data = $data;
        foreach ($this->_validators as $field => $validator) {
            if (isset($data[$field])) {
                $validator->data($data[$field]);
            }
        }
        return $this;
    }

    /**
     * Sets the <code>$label</code> of the internal validation object belonging
     * to the field specified by <code>$field</code>
     *
     * @param string $field
     * @param string $label
     * @return ValidationAggregate <code>$this</code>
     */
    public function label($field, $label) {
        if ( ! isset($this->_validators[$field])) {
            if ( ! array_key_exists($field, $this->_data))
                throw new Exception("data does not have field '$field'");

            $this->_validators[$field] = new Validation($this->_data[$field], $this->_fail_on_first);
        }
        $this->_validators[$field]->label($label);
        return $this;
    }

    /**
     * Adds a rule to the internal @c Validation object which belongs to the
     * given <code>$field</code>.
     *
     * @param string $field
     * @param string|callback $rule
     * @param array $params
     * @param string $error_msg
     * @uses Validation::rule()
     * @return ValidationAggregate <code>$this</code>
     */
    public function rule($field, $rule, $params = array(), $error_msg = NULL) {
        if ( ! isset($this->_validators[$field])) {
            if ( ! array_key_exists($field, $this->_data))
                throw new Exception("data does not have field '$field'");
            
            $this->_validators[$field] = new Validation($this->_data[$field], $this->_fail_on_first);
        }
        $this->_validators[$field]->rule($rule, $params, $error_msg);
        return $this;
    }

    public function __get($name) {
        static $enabled_attrs = array('validators', 'fail_on_first');
        if (in_array($name, $enabled_attrs)) {
            return $this->{'_' . $name};
        }
        throw new Exception("property cyclone\\ValidationAggregate::\$$name does not exists");
    }

    /**
     * Runs the internal field validators.
     *
     * @return boolean <code>TRUE</code> if all validation rules of all the internal
     *   validators passed, otherwise <code>FALSE</code>.
     */
    public function validate() {
        $valid = TRUE;
        foreach ($this->_validators as $validator) {
            if ( ! $validator->validate()) {
                $valid = FALSE;
            }
        }
        return $valid;
    }
}

