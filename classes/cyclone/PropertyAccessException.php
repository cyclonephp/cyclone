<?php
namespace cyclone;

/**
 * <code>PropertyAccessException</code>s are thrown by classes which simulate properties with magic methods.
 * <code>__set()</code> methods throw <code>PropertyAccessException</code>s with <code>PropertyAccessException::WRITE</code>
 * operation code if the referenced property is not writable or does not exist. <code>__get()</code> methods throw
 * <code>PropertyAccessException</code>s with <code>PropertyAccessException::READ</code> operation code if the
 * referenced property does not exist or is not readable.
 *
 * @author Bence Erős <crystal@cyclonephp.org>
 * @package cyclone
 * @property-read $operation string can be @c PropertyAccessException::READ or @c PropertyAccessException::WRITE
 * @property-read $class string the name of the class which threw the exception
 * @property-read $property string the name of the property which was referenced but isn't readable/writable
 */
class PropertyAccessException extends CycloneException {

    const READ = 'read';

    const WRITE = 'write';

    protected  $_operation = self::READ;

    protected $_class;

    protected $_property;

    public function __construct($class, $property, $operation = self::READ) {
        parent::__construct("class property '{$property}' of '{$class}' does not exist or is not "
            . ($operation === self::READ ? 'readable' : 'writable'));
        $this->_class = $class;
        $this->_property = $property;
        $this->_operation = $operation;
    }

    public function __get($key) {
        static $enabled_attributes = array('operation', 'class', 'property');
        if (in_array($key, $enabled_attributes))
            return $this->{'_' . $key};

        throw new PropertyAccessException(get_class($this), $key);
    }

}
