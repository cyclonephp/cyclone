<?php

namespace cyclone\view;

use cyclone as cy;

/**
 * Abstract implementation of the \c View interface. Implements all methods of
 * View and declares a new abstract protected method named <code>capture()</code>.
 * Concrete implementations of the View interface should be extended from this
 * class and in the <code>capture()</code> implementation the template should be
 * processed and the resulting HTML should be returned. <code>AbstractView</code>
 * also adds some magic methods to conveniently handle template variables.
 *
 * Most part of this class has been copied from the View class of Kohana.
 *
 * @package cyclone
 * @author Kohana Team
 * @author Bence Eros <crystal@cyclonephp.com>
 */
abstract class AbstractView implements View, \ArrayAccess {

    // Array of global variables
    protected static $_global_data = array();

    /**
     * The file name extension of the template files (including the
     * leading dot).
     *
     * @var string
     */
    protected static $_file_extension = '.php';

    public static $default = 'cyclone\\view\\PHPView';

    public static function factory($file = NULL, $data = array(), $is_absolute = FALSE) {
        $classname = self::$default;
        return new $classname($file, $data, $is_absolute);
    }


    /**
     * The path of the template
     *
     * @var string
     */
    protected $_template;
    
    // Array of local variables
    protected $_data = array();

    /**
     * @param   string $file template file name
     * @param   array $data  array of values
     * @param boolean $is_absolute should be <code>TRUE</code> if the file name
     *  is an absolute path, not a path relative to <code>views/</code>
     * @uses AbstractView::set_filename
     */
    public function __construct($file = NULL, $data = array(), $is_absolute = FALSE) {
        if ($file !== NULL) {
            $this->set_template($file, $is_absolute);
        }
        $this->_data = $data;
    }

    /**
     * Sets the template file name.
     *
     * @param   string $file template filename
     * @return  View
     * @throws  ViewException
     */
    public function set_template($file, $is_absolute = FALSE) {
        if (!$is_absolute) {
            if (($abs_file = cy\FileSystem::find_file('views/'
                            . $file
                            . static::$_file_extension)) === FALSE) {
                throw new ViewException("The requested view '$file' could not be found");
            }
        } else {
            $abs_file = $file;
        }

        $this->_template = $abs_file;

        return $this;
    }


    /**
     * Sets a global variable, similar to \c set(), except that the
     * variable will be accessible to all views.
     *
     * @param   string $key variable name or an array of variables
     * @param   mixed $value  value
     */
    public static function set_global($key, $value = NULL) {
        if (is_array($key)) {
            foreach ($key as $name => $value) {
                self::$_global_data[$name] = $value;
            }
        } else {
            self::$_global_data[$key] = $value;
        }
    }

    /**
     * Assigns a global variable by reference, similar to \c bind(), except
     * that the variable will be accessible to all views.
     *
     * @param   string $key variable name
     * @param   mixed $value referenced variable
     */
    public static function bind_global($key, & $value) {
        self::$_global_data[$key] = & $value;
    }

    /**
     * Assigns a variable by name. Assigned values will be available as a
     * variable within the view file:
     *
     * You can also use an array to set several values at once:
     * @code
     *     // Create the values $food and $beverage in the view
     *     $view->set(array('food' => 'bread', 'beverage' => 'water'));
     * @endcode
     *
     * @param   string $key variable name or an array of variables
     * @param   mixed $value the value to be assigned
     * @return  View $this
     */
    public function set($key, $value = NULL) {
        if (is_array($key)) {
            foreach ($key as $name => $value) {
                $this->_data[$name] = $value;
            }
        } else {
            $this->_data[$key] = $value;
        }

        return $this;
    }

    /**
     * Assigns a value by reference. The benefit of binding is that values can
     * be altered without re-setting them. It is also possible to bind variables
     * before they have values. Assigned values will be available as a
     * variable within the view file. Example:
     * @code
     *     // This reference can be accessed as $ref within the view
     *     $view->bind('ref', $bar);
     * @endcode
     * 
     * @param   string $key variable name
     * @param   mixed  $value  referenced variable
     * @return  AbstractView $this
     */
    public function bind($key, & $value) {
        $this->_data[$key] = & $value;
        return $this;
    }

    /**
     * Renders the view object to a string. Global and local data are merged
     * and extracted to create local variables within the view file.
     *
     * Global variables with the same key name as local variables will be
     * overwritten by the local variable.
     *
     * @param    string $file view filename
     * @return   string
     * @throws   ViewException
     */
    public function render($file = NULL) {
        if ($file !== NULL) {
            $this->set_template($file);
        }

        if (empty($this->_template))
            throw new ViewException('You must set the file to use within your view before rendering');

        // Combine local and global data and capture the output
        return $this->capture();
    }

    /**
     * Concrete subclasses should implement this method. The implementations
     * should return the template output as a string, where the absotule
     * path of the template can be accessed via <code>$this->_file</code>,
     * the template data via <code>$this->_data</code> and the global data
     * as <code>self::$_global_data</code>. Both the template data and the global
     * template data should be accessible as (global) variables in the template file.
     */
    protected abstract function capture();

    /**
     * Alias for \c render() .
     *
     * @return  string
     * @uses    render()
     */
    public function __toString() {
        try {
            return $this->render();
        } catch (\Exception $e) {
            // Display the exception message
            cy\Kohana::exception_handler($e);
            return '';
        }
    }

    /**
     * Magic method, searches for the given variable and returns its value.
     * Local variables will be returned before global variables.
     *
     * @param   string $key the variable name
     * @return  mixed
     * @throws  ViewException if the variable has not yet been set
     */
    public function & __get($key) {
        if (isset($this->_data[$key]))
            return $this->_data[$key];

        if (isset(self::$_global_data[$key]))
            return self::$_global_data[$key];
        
        throw new ViewException("Template variable is not set: '$key'");
    }

    /**
     * Magic method, calls [View::set] with the same parameters.
     *
     *     $view->foo = 'something';
     *
     * @param   string $key variable name
     * @param   mixed $value
     * @return  void
     */
    public function __set($key, $value) {
        $this->set($key, $value);
    }

    /**
     * Magic method, determines if a variable is set.
     *
     * @param   string $key variable name
     * @return  boolean
     * @uses array_key_exists()
     */
    public function __isset($key) {
        return array_key_exists($key, $this->_data)
                || array_key_existst($key, self::$_global_data);
    }

    /**
     * Magic method, unsets a given template variable.
     *
     * @param   string  variable name
     * @return  void
     */
    public function __unset($key) {
        unset($this->_data[$key], self::$_global_data[$key]);
    }

    public function offsetSet($key, $value) {
        return $this->set($key, $value);
    }

    public function offsetGet($key) {
        return $this->__get($key);
    }

    public function offsetExists($key) {
        return $this->__isset($key);
    }

    public function offsetUnset($key) {
        return $this->__unset($key);
    }

}