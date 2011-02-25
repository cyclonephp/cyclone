<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyForm
 */
class CyForm {

    /**
     *
     * @var array the model passed to the constructor. Current input values and
     * error messages are also stored in this array.
     */
    public $model;

    /**
     *
     * @var array stores the configuration (config/kform)
     */
    protected $config;

    /**
     * indicates the
     *
     * @var string
     */
    protected $progress_id;

    /**
     *
     * @param mixed $model array or the name of the file under the forms/ dir that contains the model
     * @param boolean $load_data_sources if <code>TRUE</code>, then the data sources are loaded after model loading.
     */
    public function  __construct($model, $load_data_sources = true) {
        if (is_array($model)) {
            $this->model = $model;
        } else {
            $file = Kohana::find_file('forms', $model);
            if ($file === false)
                throw new KForm_Exception('form definition not found');
            $this->model = require $file;
        }
        $this->config = Kohana::config('kform');
        $this->_init($load_data_sources);
    }

    /**
     * creates input handling objects
     * 
     * @param boolean $load_data_sources if <code>TRUE</code>, then the data sources are loaded after model loading.
     */
    protected function _init($load_data_sources) {
        foreach($this->model['fields'] as $name => &$field) {
            $type = Arr::get($field, 'type', 'text');
            $class = 'KForm_Field_'.ucfirst($type);
            if (class_exists($class)) {
                $field = new $class($this, $name, $field);
            } else  {
                $field = new KForm_Field($this, $name, $field, $type);
            }
            
            if ($load_data_sources) {
                $field->load_data_source();
            }
        }
    }

    /**
     * pushes the key => value pairs of the source array into the form fields.
     * The source should come from the data model.
     *
     * @param array $src
     */
    public function set_data($src, $save = true) {
        foreach ($src as $k => $v) {
            if (array_key_exists($k, $this->model['fields'])) {
                $this->model['fields'][$k]->set_data($v);
            }
        }
        $save && $this->_save_data($src);
    }

    /**
     * saves the business data to be edited until the next input population.
     *
     * This business data will be needed in two cases:
     * * the form does not have any fields with a key of a business data.
     *      In this case the form input (submit data) will definitely not contain
     *      this data segment and it must be loaded to return proper value.
     * * the form has got a field with this name, but the inputs are disabled
     *      on the client side and the form submit will not contain the value
     *
     * @see KForm::load_data()
     * @see KForm::load_input()
     * @see KForm::result()
     * @param array $data
     */
    protected function _save_data(array $data) {
        if (null === $this->progress_id) {
            $this->progress_id = $this->_create_progress_id();
        }

        $_SESSION[$this->config['session_key']]['progress'][$this->progress_id] = $data;
    }

    /**
     * returns the previously saved business data.
     *
     * @param string $progress_id
     * @return array
     */
    protected function _get_saved_data($progress_id) {
        $sess_key = $this->config['session_key'];
        if (array_key_exists($sess_key, $_SESSION)
                && array_key_exists($progress_id, $_SESSION[$sess_key]['progress'])) {
            $this->progress_id = $progress_id;
            return $_SESSION[$sess_key]['progress'][$progress_id];
        }
        return array();
    }

    /**
     * creates an unique identifier for the business data edit process, and puts
     * it into the form fields as a hidden input. The saved data will be reloaded
     * if an incoming input has this generated progress ID.
     *
     * @return string
     */
    protected function _create_progress_id() {
        $sess_key = $this->config['session_key'];
        if ( ! array_key_exists($sess_key, $_SESSION)) {
            $_SESSION[$sess_key] = array(
                'progress' => array(),
                'progress_counter' => 0
            );
        }
        
        if ($_SESSION[$sess_key]['progress_counter'] == 32767) {
            $_SESSION[$sess_key]['progress_counter'] = 0;
        }

        $progress_id = sha1($_SESSION[$sess_key]['progress_counter']++);
        $_SESSION[$sess_key]['progress'][$progress_id] = array();

        $input = new KForm_Field($this, $this->config['progress_key'], array(), 'hidden');
        $input->set_data($progress_id);
        $this->model['fields'] [$this->config['progress_key']] = $input;

        return $progress_id;
    }

    /**
     * loads the result of a form submission into the form fields. You will need
     * to use this method every time when you want to handle a form submission
     * with KForm.
     *
     * @param array $src
     */
    public function set_input($src, $validate = true) {
        if (array_key_exists($this->config['progress_key'], $src)) {
            $saved_data = $this->_get_saved_data($src[$this->config['progress_key']]);
        } else {
            $saved_data = array();
        }
        foreach ($this->fields as $field) {
            $field->pick_input($src, $saved_data);
        }
        if ($validate) {
            return $this->validate();
        }
        return true;
    }

    /**
     * executes all of the field validators, and returns true if all succeeded
     *
     * @return boolean true if none of the validators of any fields returned false
     */
    public function validate() {
        $valid = true;
        foreach ($this->fields as $field) {
            if ( ! $field->validate()) {
                $valid = false;
            }
        }
        return $valid;
    }

    /**
     * Returns the business data created via the last form input.
     *
     * @param string $result_type
     * @return mixed
     */
    public function get_data($result_type = 'array') {
        $result_type = Arr::get($this->model, 'result_type', $result_type);
        if ( ! is_null($this->progress_id)) {
            $saved_data = $this->_get_saved_data($this->progress_id);
        }
        if ('array' == $result_type) {
            $result = array();
            if (isset($saved_data)) {
                foreach($saved_data as $k => $v) {
                    $result[$k] = $v;
                }
            }
            foreach ($this->fields as $name => $field) {
                if ( ! is_int($name)) {
                    $result[$name] = $field->get_data();
                }
            }
        } else {
            $result = new $result_type;
            if (isset($saved_data)) {
                foreach($saved_data as $k => $v) {
                    $result->$k = $v;
                }
            }
            foreach ($this->fields as $name => $field) {
                if ( ! is_int($name)) {
                    $result->$name = $field->get_data();
                }
            }
        }
        return $result;
    }

    public function edit_mode() {
        return ! is_null($this->progress_id);
    }

    /**
     * shortcut to model access
     */
    public function __get($key) {
        return $this->model[$key];
    }

    /**
     * shortcut to model access
     */
    public function __set($key, $value) {
        $this->model[$key] = $value;
    }

    protected function before_rendering() {
        if (is_null($this->progress_id)) {
            foreach ($this->model['fields'] as $name => &$field) {
                if (Arr::get($field->model, 'on_create') == 'hide') {
                    unset($this->model['fields'][$name]);
                }
            }
        } else {
            foreach ($this->model['fields'] as $name => &$field) {
                if (Arr::get($field->model, 'on_edit') == 'hide') {
                    unset($this->model['fields'][$name]);
                }
            }
        }
        if ( ! array_key_exists('view_root', $this->model)) {
            $this->model['view_root'] = 'kform';
        }

        if ( ! array_key_exists('view', $this->model)) {
            $this->model['view'] = 'form';
        }

        if ( ! array_key_exists('attributes', $this->model)) {
            $this->model['attributes'] = array();
        }

        if ( ! array_key_exists('method', $this->model['attributes'])) {
            $this->model['attributes']['method'] = 'post';
        }

        if ( ! array_key_exists('action', $this->model['attributes'])) {
            $this->model['attributes']['action'] = '';
        }
    }

    public function render() {
        $this->before_rendering();
        $view = new View($this->model['view_root']
                .DIRECTORY_SEPARATOR.$this->model['view'], $this->model);
        return $view->render();
    }

    public function  __toString() {
        try {
            return $this->render();
        } catch (Exception $ex) {
            Kohana::exception_handler($ex);
            return '';
        }
    }
}
