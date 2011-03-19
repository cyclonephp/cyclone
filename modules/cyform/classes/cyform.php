<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyForm
 */
class CyForm {

    public static function model() {
        return new CyForm_Model;
    }

    /**
     *
     * @param string $type
     * @param string $name
     * @return CyForm_Model_Field
     */
    public static function field($type = 'text', $name = NULL) {
        $candidate = 'CyForm_Model_Field_' . $type;
        if (class_exists($candidate)) {
            $class = $candidate;
            return new $class($name);
        } 
        return new CyForm_Model_Field($type, $name);
    }

    public static function source($callback) {
        return new CyForm_Model_DataSource($callback);
    }

    /**
     * Used as default view root if a requested template is not found
     * in the view root of the current theme.
     *
     * @usedby CyForm::render()
     * @usedby CyForm_Field::render()
     */
    const DEFAULT_THEME = 'cyform/default';

    /**
     *
     * @var CyForm_Model the model passed to the constructor. Current input values and
     * error messages are also stored in this array.
     */
    public $_model;

    public $_fields = array();

    /**
     *
     * @var array stores the configuration (config/cyform)
     */
    protected $_config;

    /**
     * indicates the
     *
     * @var string
     */
    protected $_progress_id;

    /**
     *
     * @param mixed $model array or the name of the file under the forms/ dir that contains the model
     * @param boolean $load_data_sources if <code>TRUE</code>, then the data sources are loaded after model loading.
     */
    public function  __construct($model, $load_data_sources = true) {
        if (is_string($model)) {
            $file = FileSystem::find_file('forms/' . $model . '.php');
            if (FALSE === $file)
                throw new CyForm_Exception("form not found: $model");
        }

        if (  ! ($model instanceof CyForm_Model))
            throw new CyForm_Exception('invalid model');

        $this->_model = $model;

        $this->_config = Config::inst()->get('cyform');
        $this->init($load_data_sources);
        $this->add_assets();
    }

    /**
     * creates input handling objects
     * 
     * @param boolean $load_data_sources if <code>TRUE</code>, then the data sources are loaded after model loading.
     */
    protected function init($load_data_sources) {
        foreach($this->_model->fields as $name => $field_model) {
            $class = 'CyForm_Field_'.ucfirst($field_model->type);
            if (class_exists($class)) {
                $field = new $class($this, $name, $field_model);
            } else  {
                $field = new CyForm_Field($this, $name, $field_model);
            }
            
            if ($load_data_sources) {
                $field->load_data_source();
            }
            if (NULL === $field_model->name) {
                $this->_fields []= $field;
            } else {
                $this->_fields[$field_model->name] = $field;
            }
        }
    }

    protected function add_assets() {
        if (NULL === $this->_model->theme) {
            $this->_model->theme = self::DEFAULT_THEME;
        }
        try {
            Asset_Pool::inst()->add_asset($theme, 'css');
        } catch (Exception $ex) {}
        try {
            Asset_Pool::inst()->add_asset($theme, 'js');
        } catch (Exception $ex) {}
    }

    /**
     * pushes the key => value pairs of the source array into the form fields.
     * The source should come from the data model.
     *
     * @param array $src
     */
    public function set_data($src, $save = true) {
        foreach ($src as $k => $v) {
            if (array_key_exists($k, $this->_fields)) {
                $this->_fields[$k]->set_data($v);
            }
        }
        $save && $this->save_data($src);
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
     * @see CyForm::load_data()
     * @see CyForm::load_input()
     * @see CyForm::result()
     * @param array $data
     */
    protected function save_data(array $data) {
        if (null === $this->_progress_id) {
            $this->_progress_id = $this->create_progress_id();
        }

        $_SESSION[$this->_config['session_key']]['progress'][$this->_progress_id] = $data;
    }

    /**
     * returns the previously saved business data.
     *
     * @param string $progress_id
     * @return array
     */
    protected function get_saved_data($progress_id) {
        $sess_key = $this->_config['session_key'];
        if (array_key_exists($sess_key, $_SESSION)
                && array_key_exists($progress_id, $_SESSION[$sess_key]['progress'])) {
            $this->_progress_id = $progress_id;
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
    protected function create_progress_id() {
        $sess_key = $this->_config['session_key'];
        if ( ! array_key_exists($sess_key, $_SESSION)) {
            $_SESSION[$sess_key] = array(
                'progress' => array(),
                'progress_counter' => 0
            );
        }
        
        if (32767 == $_SESSION[$sess_key]['progress_counter']) {
            $_SESSION[$sess_key]['progress_counter'] = 0;
        }

        $progress_id = sha1($_SESSION[$sess_key]['progress_counter']++);
        $_SESSION[$sess_key]['progress'][$progress_id] = array();

        // creating hidden input for storing unique form ID
        $field_model = new CyForm_Model_Field('hidden'
             , $this->_config['progress_key']);

        $field = new CyForm_Field($this, $this->_config['progress_key']
                , $field_model);
        $field->set_data($progress_id);
        // and adding it to the form inputs
        $this->_model->fields[$this->_config['progress_key']] = $field_model;

        $this->_fields[$this->_config['progress_key']] = $field;

        return $progress_id;
    }

    /**
     * loads the result of a form submission into the form fields. You will need
     * to use this method every time when you want to handle a form submission
     * with CyForm.
     *
     * @param array $src
     */
    public function set_input($src, $validate = true) {
        if (array_key_exists($this->_config['progress_key'], $src)) {
            $saved_data = $this->get_saved_data($src[$this->_config['progress_key']]);
        } else {
            $saved_data = array();
        }
        foreach ($this->_fields as $field) {
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
        foreach ($this->_fields as $field) {
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
        $result_type = $this->_model->result_type;
        if ( ! is_null($this->_progress_id)) {
            $saved_data = $this->get_saved_data($this->_progress_id);
        }
        if ('array' == $result_type) {
            $result = array();
            if (isset($saved_data)) {
                foreach($saved_data as $k => $v) {
                    $result[$k] = $v;
                }
            }
            foreach ($this->_fields as $name => $field) {
                // if the key is an integer then it's the hidden input created
                // for storing the form ID. This value shouldn't be presented in
                // the business data
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
            foreach ($this->_fields as $name => $field) {
                // the same here
                if ( ! is_int($name)) {
                    $result->$name = $field->get_data();
                }
            }
        }
        return $result;
    }

    /**
     * Checks if the form is currently in edit mode or not.
     *
     * @return boolean
     */
    public function edit_mode() {
        return ! is_null($this->_progress_id);
    }

    protected function before_rendering() {
        if (is_null($this->_progress_id)) {
            foreach ($this->_model->fields as $name => &$field) {
                if ($field->on_create == 'hide') {
                    unset($this->_fields[$name]);
                }
            }
        } else {
            foreach ($this->_model->fields as $name => &$field) {
                if ($field->on_edit == 'hide') {
                    unset($this->_fields[$name]);
                }
            }
        }
    }

    public function render() {
        $this->before_rendering();
        try {
            $view = new View($this->_model->theme
                .DIRECTORY_SEPARATOR.$this->_model->view, array(
                    'model' => $this->_model,
                    'fields' => $this->_fields
                ));
        } catch (Kohana_View_Exception $ex) {
            $view = new View(self::DEFAULT_THEME . DIRECTORY_SEPARATOR
                    . $this->_model->view, array(
                    'model' => $this->_model,
                    'fields' => $this->_fields
                ));
        }
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
