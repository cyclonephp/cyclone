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
     * Compiles a DB_Query_Select to SQL according to the SQL dialect of the
     * DBMS. Recommended to use DB_Query_Select::compile() instead.
     *
     * @param DB_Query_Select $query
     * @return string the generated SQL
     * @usedby DB_Query_Select::compile()
     */
    public function  compile_select(DB_Query_Select $query) {
        $this->select_aliases($query->tables, $query->joins);
        $rval = 'SELECT ';
        if ($query->distinct) {
            $rval .= 'DISTINCT ';
        }
        $rval .= $this->escape_values($query->columns);
        $rval .= ' FROM ';
        $tbl_names = array();
        foreach ($query->tables as $table) {
            $tbl_names []= $this->escape_table($table);
        }
        $rval .= implode(', ', $tbl_names);
        if ( ! empty($query->hints)){
            $rval .= $this->compile_hints($query->hints);
        }
        if ( ! empty($query->joins)) {
            foreach ($query->joins as $join) {
                $rval .= ' ' . $join['type'] . ' JOIN ' . $this->escape_table($join['table']);
                $rval .= ' ON ' . $this->compile_expressions($join['conditions']);
            }
        }
        if ( ! empty($query->where_conditions)) {
            $rval .= ' WHERE '.$this->compile_expressions($query->where_conditions);
        }
        if ( ! empty($query->group_by)) {
            $rval .= ' GROUP BY '.$this->escape_values($query->group_by);
        }
        if ( ! empty($query->having_conditions)) {
            $rval .= ' HAVING '.$this->compile_expressions($query->having_conditions);
        }
        if ( ! empty($query->order_by)) {
            $rval .= ' ORDER BY ';
            foreach ($query->order_by as $ord) {
                $rval .= $this->escape_value($ord['column']).' '.$ord['direction'];
            }
        }
        if ( ! is_null($query->limit)) {
            $rval .= ' LIMIT '.$query->limit;
        }
        if ( ! is_null($query->offset)) {
            $rval .= ' OFFSET '.$query->offset;
        }
        if ( ! empty($query->unions)) {
            foreach($query->unions as $union) {
                $rval .= ' UNION ';
                if ($union['all'] == TRUE) {
                    $rval .= 'ALL ';
                }
                $rval .= $this->compile_select($union['select']);
            }
        }
        return $rval;
    }

    /**
     * Compiles a DB_Query_Insert to SQL according to the SQL dialect of the
     * DBMS. Recommended to use DB_Query_Insert::compile() instead.
     *
     * @param DB_Query_Insert $query
     * @return string the generated SQL
     * @usedby DB_Query_Insert::compile()
     */
    public function  compile_insert(DB_Query_Insert $query) {
        $this->select_aliases($query->table);
        $rval = 'INSERT INTO ';
        $rval .= $this->escape_table($query->table);
        if (empty($query->values))
            throw new DB_Exception('no value lists to be inserted');
        $rval .= ' ('.$this->escape_values(array_keys($query->values[0])).') VALUES ';
        foreach ($query->values as $value_set) {
            $value_sets []= '('.$this->escape_params($value_set).')';
        }

        $rval .= implode(', ', $value_sets);
        return $rval;
    }

    /**
     * Compiles a DB_Query_Update to SQL according to the SQL dialect of the
     * DBMS. Recommended to use DB_Query_Update::compile() instead.
     *
     * @param DB_Query_Update $query
     * @return string the generated SQL
     * @usedby DB_Query_Update::compile()
     */
    public function  compile_update(DB_Query_Update $query) {
        $this->select_aliases($query->table);
        $rval = 'UPDATE ';
        $rval .= $this->escape_table($query->table);
        $rval .= ' SET ';
        foreach ($query->values as $k => $v) {
            $pieces []= $this->escape_identifier($k).' = '.$this->escape_param($v);
        }
        $rval .= implode(', ', $pieces);
        if ( ! empty($query->conditions)) {
            $rval .= ' WHERE '.$this->compile_expressions($query->conditions);
        }
        if ( ! is_null($query->limit)) {
            $rval .= ' LIMIT '.$query->limit;
        }
        return $rval;
    }

    /**
     * Compiles a DB_Query_Delete to SQL according to the SQL dialect of the
     * DBMS. Recommended to use DB_Query_Delete::compile() instead.
     *
     * @param DB_Query_Delete $query
     * @return string the generated SQL
     * @usedby DB_Query_Delete::compile()
     */
    public function compile_delete(DB_Query_Delete $query) {
        $this->select_aliases($query->table);
        $rval = 'DELETE FROM ';
        $rval .= $this->escape_table($query->table);
        if ( ! empty($query->conditions)) {
            $rval .= ' WHERE '.$this->compile_expressions($query->conditions);
        }
        if ( ! is_null($query->limit)) {
            $rval .= ' LIMIT '.$query->limit;
        }
        return $rval;
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

    protected abstract function compile_alias($expr, $alias);

    protected abstract function compile_hints($hints);

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

    protected function escape_values($columns) {
        foreach ($columns as $column) {
            if (is_array($column)) {
                $expr = $column[0];
                $alias = $column[1];
                if ($expr instanceof DB_Expression) {
                    $expr = '('.$expr->compile_expr($this).')';
                } else {
                    $expr = $this->escape_identifier($expr);
                }
                $escaped_cols []= $this->compile_alias($expr, $alias);
            } else {
                if ($column instanceof DB_Expression) {
                    $escaped_cols []= $column->compile_expr($this);
                } else {
                    $escaped_cols []= $this->escape_identifier($column);
                }
            }
        }
        return implode(', ', $escaped_cols);
    }

    protected function escape_value($val) {
        if (is_array($val)) {
            $expr = $val[0];
            $alias = $val[1];
            if ($expr instanceof DB_Expression) {
                $expr = '(' . $expr->compile_expr($this) . ')';
            } else {
                $expr = $this->escape_identifier($expr);
            }
            return $this->compile_alias($expr, $alias);
        } else {
            if ($val instanceof DB_Expression) {
                return $val->compile_expr($this);
            } else {
                return $this->escape_identifier($val);
            }
        }
    }

    protected function escape_params($params) {
        foreach ($params as $param) {
            $escaped_params []= $this->escape_param($param);
        }
        return implode(', ', $escaped_params);
    }

    /**
     * @param string $identifier database table or column name
     * @access package
     */
    public function escape_identifier($identifier) {
        if ($identifier instanceof DB_Expression)
            return $identifier->compile_expr($this);

        $esc_char = $this->esc_char;

        $segments = explode('.', $identifier);
        $rval = $esc_char . $segments[0] . $esc_char;
        if(array_key_exists('prefix', $this->config) && count($segments) == 2){
            if( ! in_array($segments[0], $this->_table_aliases)){
                $rval =  $esc_char . $this->config['prefix'].$segments[0] . $esc_char;
            }
        }

        if (count($segments) > 1) {
            if ('*' == $segments[1]) {
                $rval .= '.*';
            } else {
                $rval .= '.' . $esc_char . $segments[1] . $esc_char;
            }
        }
        return $rval;
    }

    /**
     * This method is responsible for prventing SQL injection.
     *
     * @param string $param user parameter that should be escaped
     */
    protected abstract function escape_param($param);

    protected abstract function escape_table($table);

    protected function compile_expressions($expr_list) {
        $compiled_exprs = array();
        foreach ($expr_list as $expr) {
            $compiled_exprs []= $expr->compile_expr($this);
        }
        return implode(' AND ', $compiled_exprs);
    }

    protected function select_aliases($tables, $joins = NULL){
        if(is_array($tables)){
            foreach($tables as $table){
                if(is_array($table)){
                    $this->_table_aliases[] = $table[1];
                }
            }
        }
        if (is_array($joins)){
            foreach($joins as $join){
                if(is_array($join['table'])){
                    $this->_table_aliases[] = $join['table'][1];
                }
            }
        }
    }

}
