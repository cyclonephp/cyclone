<?php


class KForm_Input {

    /**
     *
     * @var array the field model defined in the form definition
     */
    public $model;

    /**
     *
     * @var string the name of the field
     */
    public $name;

    /**
     *
     * @var string the input type
     */
    public $type;

    /**
     * @var mixed the current field value
     */
    public $value;

    /**
     * @var array validator - error message pairs. The validator is the order num.
     * of the validator for callback validators
     */
    public $validation_errors = array();

    /**
     *
     * @param string $name the name of the input field
     * @param array $model the field definition
     * @param string $type the type of the HTML input
     */
    public function  __construct($name, array $model, $type) {
        $this->model = $model;
        $this->name = $name;
        $this->type = $type;
    }

    public function load_data_source() {
        
    }

    public function set_val($val) {
        $this->value = $val;
    }

    public function get_val() {
        return $this->value;
    }

    /**
     *
     * @param array $src the main array the form is populated from, e.g. it can
     * be the $_POST array in a lot of cases. All the form data is visible for
     * this method, it can extract any kind of data from it.
     */
    public function pick_val(&$src, $saved_data = array()) {
        $this->value = Arr::get($src, $this->name);
        if (null === $this->value) {
            $this->set_val(Arr::get($saved_data, $this->name));
        }
    }

    /**
     * the reverse of pick_val(), it pushes the current field value into the 
     * source inputs
     *
     * @param array $src
     */
    public function push_val(&$src) {
        $src[$this->name] = $this->value;
    }

    /**
     * if the validation is set up for the field, then executes all of the
     * validators by calling <code>KForm_Input::exec_basic_validator()</code> and
     * <code>KForm_Input::exec_callback_validator()</code>.
     *
     * Stores the error messages in the <code>KForm_Input::validation_errors</code> array.
     */
    public function validate() {
        if (array_key_exists('validation', $this->model)) {
            foreach ($this->model['validation'] as $validator => $details) {
                if (is_int($validator)) { // custom callback validator
                    $this->exec_callback_validator($validator, $details);
                } else { // normal validator - using the Validate class
                    $this->exec_basic_validator($validator, $details);
                }
            }
        }
    }

    protected function exec_basic_validator($validator, $details) {
        $callback = array('Validate', $validator);
        if (is_array($details)) {
            $params = Arr::get($details, 'params', array());
            array_unshift(&$params, $this->value);
            if (array_key_exists('error', $details)) {
                $error = $details['error'];
            }
        } else {
            $params = array($this->value);
        }
        $result = call_user_func_array($callback, $params);
        if ( ! $result) {
            if ( ! isset($error)) {
                $error = __(Kohana::config('kform.default_error_prefix') . $validator);
            }
            $this->add_validation_error($validator, $error, $params);
        }
    }

    protected function exec_callback_validator($validator, $details) {
        if ( ! is_array($details))
            throw new KForm_Exception($details.' is not an array');

        if ( ! array_key_exists('params', $details)) {
            $params = array();
        } else {
            $params = $details['params'];
        }
        array_unshift($params, $this->value);
        $result = call_user_func_array($details['callback'], $params);
        if ( ! $result) {
            if ( ! array_key_exists('error', $details)) {
                $error = __(Kohana::config('kform.default_error_prefix') . $validator);
            } else {
                $error = $details['error'];
            }
            $this->add_validation_error($validator, $error, $params);
        }
    }

    protected function add_validation_error($validator, $error_template, $params) {
        foreach ($params as $k => $v) {
            $error_template = str_replace(':' . ($k + 1), $v, $error_template);
        }
        $this->validation_errors[$validator] = $error_template;
    }

}