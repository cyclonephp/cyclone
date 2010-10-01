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
        $this->init($load_data_sources);
    }

    /**
     * creates input handling objects
     * 
     * @param boolean $load_data_sources if <code>TRUE</code>, then the data sources are loaded after model loading.
     */
    protected function init($load_data_sources) {
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

    public function result() {
        
    }

    public function __get($key) {
        return $this->model[$key];
    }

    public function __set($key, $value) {
        $this->model[$key] = $value;
    }
}
