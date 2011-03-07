<?php
/**
 * Abstract class for DBMS-specific adapters.
 *
 * Adapter classes are responsible for compiling DB_Query instances to proper
 * SQL according to the DBMS and calling the appropriate PHP functions to execute
 * the SQL. They provide a common, database-independent API.
 *
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
abstract class DB_Adapter {

    protected $config;
    
    protected $_table_aliases = array();

    protected $esc_char;

    /**
     * DB_Adapter classes are recommended to be created using DB::inst() instead
     * of direct instantiation.
     *
     * @param array $config
     * @access package
     * @see DB::inst()
     */
    public function  __construct($config) {
        $this->config = $config;
        $this->connect();
    }

    /**
     * Connects to the database.
     */
    protected abstract function connect();

    /**
     * Disconnects from the database.
     */
    public abstract function disconnect();

    /**
     * Calls a compile_* method according to the class of $query.
     *
     * @param DB_Query $query
     * @return string the generated SQL
     */
    public function compile($query) {
        switch (get_class($query)) {
            case 'DB_Query_Select':
                return $this->compile_select($query);
            case 'DB_Query_Insert':
                return $this->compile_insert($query);
            case 'DB_Query_Update':
                return $this->compile_update($query);
            case 'DB_Query_Delete':
                return $this->compile_delete($query);
            default:
                throw new DB_Exception('unknown query type');
        }
    }

    /**
     * Calls an exec_* method according to the class of the query.
     *
     * @param DB_Query $query
     * @return mixed it's up to the query type
     */
    public function exec($query) {
        switch (get_class($query)) {
            case 'DB_Query_Select':
                return $this->exec_select($query);
            case 'DB_Query_Insert':
                return $this->exec_insert($query);
            case 'DB_Query_Update':
                return $this->exec_update($query);
            case 'DB_Query_Delete':
                return $this->exec_delete($query);
            default:
                throw new DB_Exception('unknown query type');
        }
    }


    /**
     * Compiles and executes an SQL select query.
     *
     * Recommended to use DB_Query_Select::exec() instead.
     *
     * @param DB_Query_Select $query the query to be executed.
     * @return DB_Query_Result
     * @uses DB_Adapter::compile_select()
     * @usedby DB_Query_Select::exec()
     */
    abstract function exec_select(DB_Query_Select $query);

    /**
     * Compiles and executes an SQL insert statement.
     *
     * Recommended to use DB_Query_Insert::exec() instead. Returns the primary
     * key of the last inserted row if $return_insert_id is true. It can come
     * with significant performance loss for some adapters.
     *
     * @param DB_Query_Insert $query the query to be executed.
     * @param boolean $return_insert_id if FALSE then the return value will be NULL.
     * @return integer the primary key of the inserted row, or NULL.
     * @uses DB_Adapter::compile_insert()
     * @usedby DB_Query_Insert::exec()
     */
    abstract function exec_insert(DB_Query_Insert $query, $return_insert_id);

    /**
     * Compiles and executes an SQL update statement.
     *
     * Recommended to use DB_Query_Update::exec() instead.
     *
     * @param DB_Query_Update $query the query to be executed.
     * @return integer the number of affected rows.
     * @uses DB_Adapter::compile_update()
     * @usedby DB_Query_Update::exec()
     */
    abstract function exec_update(DB_Query_Update $query);

    /**
     * Compiles and executes an SQL delete statement.
     *
     * Recommended to use DQ_Query_Delete::exec() instead.
     * 
     * @param DB_Query_Delete $query the statement to be executed
     * @return integer the number of deleted rows
     * @uses DB_Adapter::compile_delete()
     * @usedby DB_Query_Delete::exec()
     */
    abstract function exec_delete(DB_Query_Delete $query);

    /**
     * Executes any kind of SQL statements.
     *
     * Executes any kind of SQL statements that are not one of SELECT / INSERT
     * / UPDATE / DELETE, or can't be put together using the query builder
     * (DB_Query_*) classes (for example because the statement is very 
     * DBMS-specific, like MySQL-s TRUNCATE). Different adapters' behavior may
     * be different.
     */
    abstract function exec_custom($sql);

    /**
     * Sets autocommit mode of the current connection (session).
     *
     * This method is not supported by all database adapters.
     *
     * @param boolean $autocommit
     */
    abstract function autocommit($autocommit);

    /**
     * Commits the current transaction.
     */
    abstract function commit();

    /**
     * Rolls back the current transaction
     */
    abstract function rollback();

    public abstract function prepare_select($sql);

    public abstract function prepare_insert($sql);

    public abstract function prepare_update($sql);

    public abstract function prepare_delete($sql);

}
