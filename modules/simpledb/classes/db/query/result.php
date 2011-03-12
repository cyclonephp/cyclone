<?php

/**
 * The base class for database result processors.
 *
 * Database result classes provide a simple and convenient way to iterate on the
 * result of a SELECT query. Every database adapter has it's own implementation
 * of DB_Query_Result. Result objects are recommended to not be created directly,
 * but via the exec() method of DB_Query_Select.
 *
 * Example:
 * <code>
 * $result = DB::select()->from('t_users')->exec()
 *      ->rows('Model_User')->index_by('id);
 * foreach ($result as $id => $user) {
 *    echo "user #$id: {$user->name}";
 * }
 * </code>
 *
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
abstract class DB_Query_Result extends ArrayIterator implements Countable, Traversable {

    protected $_row_type = 'array';

    protected $_index_by;

    protected $_current_row;

    protected $_idx = -1;

    /**
     * Sets the row type to be used during the iteration.
     *
     * It can be a valid class name, or 'array'. The latter is the default.
     *
     * @param string $type
     * @return DB_Query_Result $this
     */
    public function rows($type) {
        $this->_row_type = $type;
        return $this;
    }

    /**
     * Sets the column of the database result to be used as index key during the
     * iteration.
     *
     * By default it's NULL. If it's NULL, then the key will be the number of the
     * currently processed row. It's useful to set it to a primary key (if it's
     * selected).
     *
     * @param string $column
     * @return DB_Query_Result $this
     */
    public function index_by($column) {
        $this->_index_by = $column;
        return $this;
    }


    /**
     * Returns all the result rows as associative arrays.
     *
     * @return array
     */
    public abstract function as_array();

}
