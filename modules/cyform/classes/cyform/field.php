<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyForm
 */
class CyForm_Field {

    /**
     *
     * @var CyForm_Model_Field the field model defined in the form definition
     */
    public $_model;

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
     * set at the constructor
     *
     * @var CyForm
     */
    protected $_form;

    /**
     *
     * @var cyform configuration
     */
    protected $_cfg;

    /**
     *
     * @param string $name the name of the input field
     * @param array $model the field definition
     * @param string $type the type of the HTML input
     */
    public function  __construct(CyForm $form, $name
            , CyForm_Model_Field $model, $cfg) {
        $this->_form = $form;
        $this->_model = $model;
        $this->_cfg = $cfg;
    }

    /**
     * Empty method. Can be overriden by subclasses if the input type represented
     * by the subclass has got data source to be loaded. A CyForm object loads
     * the data sources of its fields on creation in most cases.
     *
     * @usedby CyForm::init()
     */
    public function load_data_source() {
        
    }

    /**
     * Default implementation that works for most inputs. It can be overriden by
     * subclasses.
     *
     * @param mixed $val
     */
    public function set_data($val) {
        $this->value = $val;
    }

    /**
     * Default implementation that works for most inputs. It can be overriden by
     * subclasses.
     * 
     * @return mixed
     */
    public function get_data() {
        return $this->value;
    }

    /**
     *
     * @param array $src the main array the form is populated from, e.g. it can
     * be the $_POST array in a lot of cases. All the form data is visible for
     * this method, it can extract any kind of data from it.
     * @param array $saved_data the business data saved before form rendering, or
     * an empty array. The field must take it's value from this array if it can't
     * find the required input values in $src. It can happen if the input(s) were
     * disabled on the client side therefore weren't submitted.
     */
    public function pick_input(&$src, &$saved_data = array()) {
        $this->value = Arr::get($src, $this->_model->name);
        if (null === $this->value) {
            $this->set_data(Arr::get($saved_data, $this->_model->name));
        }
        if ('' === $this->value) {
            $this->value = $this->_model->on_empty;
        }
    }

    /**
     * the reverse of pick_val(), it pushes the current field value into the 
     * source inputs
     *
     * @param array $src
     */
    public function push_input(&$src) {
        $src[$this->_model->name] = $this->value;
    }

    /**
     * if the validation is set up for the field, then executes all of the
     * validators by calling CyForm_Input::exec_basic_validator() and
     * CyForm_Input::exec_callback_validator().
     *
     * Stores the error messages in the <code>CyForm_Input::validation_errors</code> array.
     */
    public function validate() {
        $policy = $this->_cfg['validation_policy'];
        $is_valid = TRUE;
        foreach ($this->_model->validators as $validator => $details) {
            if (is_int($validator)) { // custom callback validator
                $valid = $this->exec_callback_validator($validator, $details);
            } else { // normal validator - using the Validate class
                $valid = $this->exec_basic_validator($validator, $details);
            }
            if ( ! $valid ) {
                if ($policy == 'fail_on_first')
                    return FALSE;
                $is_valid = FALSE;
            }
        }
        return $is_valid;
    }

    protected function exec_basic_validator($validator, $details) {
        $callback = array('Validate', $validator);
        if (is_array($details)) {
            $params = Arr::get($details, 'params', array());
            if (TRUE === $params) {
                $params = array($this->value);
            } else {
                array_unshift($params, $this->value);
            }
            if (isset($details['error'])) {
                $error = $details['error'];
            }
        } else {
            $params = array($this->value);
        }
        $result = call_user_func_array($callback, $params);
        if ( ! $result) {
            if ( ! isset($error)) {
                $error = __(Kohana::config('cyform.default_error_prefix') . $validator);
            }
            $this->add_validation_error($validator, $error, $params);
            return FALSE;
        }
        return TRUE;
    }

    protected function exec_callback_validator($validator, $details) {
        if ( ! is_array($details))
            throw new CyForm_Exception($details.' is not an array');

        if ( ! array_key_exists('params', $details)) {
            $params = array();
        } else {
            $params = $details['params'];
        }
        array_unshift($params, $this->value);
        $result = call_user_func_array($details['callback'], $params);
        if ( ! $result) {
            if ( ! array_key_exists('error', $details)) {
                $error = __(Kohana::config('cyform.default_error_prefix') . $validator);
            } else {
                $error = $details['error'];
            }
            $this->add_validation_error($validator, $error, $params);
            return FALSE;
        }
        return TRUE;
    }

    protected function add_validation_error($validator, $error_template, $params) {
        foreach ($params as $k => $v) {
            $error_template = str_replace(':' . ($k + 1), $v, $error_template);
        }
        $this->validation_errors[$validator] = $error_template;
    }

    /**
     * Prepares the field model for rendering.
     *
     * @return void
     * @usedby CyForm_Field::render()
     */
    protected function before_rendering() {
        $this->_model->errors = $this->validation_errors;
        
        if (( ! $this->_form->edit_mode()
                && 'disable' == $this->_model->on_create)
            || ($this->_form->edit_mode()
                && 'disable' == $this->_model->on_edit)) {
            
            $this->_model->attributes['disabled'] = 'disabled';
        }
        $this->_model->attributes['value'] = $this->value;
        $this->_model->attributes['name'] = $this->_model->name;
        $this->_model->attributes['type'] = $this->_model->type;
        if (NULL === $this->_model->view) {
            $this->_model->view = $this->_model->type;
        }
    }

    /**
     * Renders the field.
     *
     * @return string
     * @uses CyForm_Field::before_rendering()
     */
    public function render() {
        $this->before_rendering();
        try {
            $view = new View($this->_form->_model->theme
                .DIRECTORY_SEPARATOR.$this->_model->view,
                (array) $this->_model);
        } catch (Kohana_View_Exception $ex) {
            $view = new View(CyForm::DEFAULT_THEME . DIRECTORY_SEPARATOR
                    . $this->_model->view, (array) $this->_model);
        }
        return $view->render();
    }

    public function __toString() {
        try {
            return $this->render();
        } catch (Exception $ex) {
            Kohana::exception_handler($ex);
            return '';
        }
    }

}
