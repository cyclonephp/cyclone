<?php
namespace cyclone;

/**
 * Array and variable validation.
 *
 * @package    cyclone
 * @author     Bence Erős <crystal@cyclonephp.org>
 */
class Validation {

    /**
     * @return Validation
     */
    public static function factory($data = NULL, $fail_on_first = FALSE) {
        return new Validation($data, $fail_on_first);
    }

    /**
     * @var array
     */
    protected $_rules;

    /**
     * @var boolean
     */
    protected $_fail_on_first;

    /**
     *
     * @var scalar
     */
    protected $_data;

    /**
     * @var array<string>
     */
    protected $_errors;

    /**
     *
     * @var string
     */
    public $label;

    public function __get($name) {
        static $_enabled_attrs = array('errors');
        if (in_array($name, $_enabled_attrs)) {
            return $this->{'_' . $name};
        }
        throw new cy\Exception('property "' . $name . '" does not exist');
    }


    public function __construct($data = NULL, $fail_on_first = FALSE) {
        $this->_data = $data;
        $this->_fail_on_first = $fail_on_first;
    }

    /**
     *
     * @param string|callback $rule
     * @param array $params
     * @param string $error_msg
     * @return Validation
     */
    public function rule($rule, $params = array(), $error_msg = NULL) {
        if ( ! is_string($rule) && NULL === $error_msg) {
            throw new cy\Exception("error_msg must be specified for callback validation rules");
        }
        $this->_rules []= array(
            'rule' => $rule,
            'params' => $params === NULL ? array() : $params ,
            'error_msg' => $error_msg
        );
        return $this;
    }

    /**
     *
     * @param mixed $data
     * @return Validation
     */
    public function data($data) {
        $this->_data = $data;
        return $this;
    }

    /**
     * @return Validation
     * @param string $label
     */
    public function label($label) {
        $this->label = $label;
        return $this;
    }

    /**
     * @param boolean $fail_on_first
     * @return Validation
     */
    public function fail_on_first($fail_on_first) {
        $this->_fail_on_first = $fail_on_first;
        return $this;
    }

    /**
     * @return boolean
     */
    public function validate() {
        $this->_errors = array();
        $valid = TRUE;
        foreach ($this->_rules as $cnt) {
            $rule = $cnt['rule'];
            $params = $cnt['params'];
            $error_msg = $cnt['error_msg'];

            if (is_string($rule)) {
                $callback = array('cyclone\\Valid', $rule);
            } else {
                $callback = $rule;
            }
            array_unshift($params, $this->_data);
            $valid = call_user_func_array($callback, $params);
            if ( ! $valid) {
                if (NULL === $error_msg) {
                    if ( ! is_string($rule))
                        throw new cy\Exception("error_msg must be specified for callback validation rules");
                    $error_msg = 'error.' . $rule;
                }
                $error_msg = __($error_msg);

                $error_params = array(
                    ':value' => $this->_data,
                    ':label' => $this->label
                );
                foreach ($params as $idx => $value) {
                    $error_params[':' . $idx] = $value;
                }

                $error_msg = strtr($error_msg, $error_params);
                $this->_errors []= $error_msg;

                if ($this->_fail_on_first) {
                    return FALSE;
                } else {
                    $valid = FALSE;
                }
            }
        }
        return $valid;
    }

    /**
     * Same as @c validate()
     *
     * @return boolean
     */
    public function __invoke() {
        return $this->validate();
    }
	
}
