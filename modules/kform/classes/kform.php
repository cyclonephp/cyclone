<?php


class KForm {

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
            $class = 'KForm_Input_'.ucfirst($type);
            if (class_exists($class)) {
                $field = new $class($name, $field);
            } else  {
                $field = new KForm_Input($name, $field, $type);
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
    public function load_data($src, $save = true) {
        foreach ($src as $k => $v) {
            if (array_key_exists($k, $this->model['fields'])) {
                $this->model['fields'][$k]->set_val($v);
            }
        }
        $save && $this->_save_data($src);
    }

    protected function _save_data(array $data) {
        if (null === $this->progress_id) {
            $this->progress_id = $this->_create_progress_id();
        }

        $_SESSION[$this->config['session_key']]['progress'][$this->progress_id] = $data;
    }

    protected function _get_saved_data($progress_id) {
        $sess_key = $this->config['session_key'];
        if (array_key_exists($sess_key, $_SESSION)
                && array_key_exists($progress_id, $_SESSION[$sess_key]['progress'])) {
            $this->progress_id = $progress_id;
            return $_SESSION[$sess_key]['progress'][$progress_id];
        }
        return array();
    }

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

        $input = new KForm_Input($this->config['progress_key'], array(), 'hidden');
        $input->set_val($progress_id);
        $this->model['fields'] [$this->config['progress_key']] = $input;

        return $progress_id;
    }

    /**
     * loads the result of a form submission into the form fields. You will need
     * to use this method every time when you want to handle a form submitssion
     * with KForm.
     *
     * @param array $src
     */
    public function load_input($src, $validate = true) {
        if (array_key_exists($this->config['progress_key'], $src)) {
            $saved_data = $this->_get_saved_data($src[$this->config['progress_key']]);
        } else {
            $saved_data = array();
        }
        foreach ($this->fields as $field) {
            $field->pick_val($src, $saved_data);
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

    public function result($result_type = 'array') {
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
                    $result[$name] = $field->get_val();
                }
            }
        } else {
            $result = new $result_type;
            foreach ($this->fields as $name => $field) {
                if ( ! is_int($name)) {
                    $result->$name = $field->get_val();
                }
            }
        }
        return $result;
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
}
