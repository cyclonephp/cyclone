<?php

abstract class DB_Adapter {

    protected $config;

    public function  __construct($config) {
        $this->config = $config;
        $this->connect();
    }

    protected abstract function connect();

    public abstract function disconnect();

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


    abstract function compile_select(DB_Query_Select $query);

    abstract function compile_insert(DB_Query_Insert $query);

    abstract function compile_update(DB_Query_Update $query);

    abstract function compile_delete(DB_Query_Delete $query);

    abstract function exec_select(DB_Query_Select $query);

    abstract function exec_insert(DB_Query_Insert $query);

    abstract function exec_update(DB_Query_Update $query);

    abstract function exec_delete(DB_Query_Delete $query);

    abstract function compile_alias($expr, $alias);

    /**
     * @param boolean $autocommit
     */
    abstract function autocommit($autocommit);

    abstract function commit();

    abstract function rollback();

    public function escape_values($columns) {
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

    public function escape_value($val) {
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

    public function escape_params($params) {
        foreach ($params as $param) {
            $escaped_params []= $this->escape_param($param);
        }
        return implode(', ', $escaped_params);
    }

    /**
     * @param string $identifier database table or column name
     */
    public abstract function escape_identifier($identifier);

    /**
     * This method is responsible for prventing SQL injection.
     *
     * @param string $param user parameter that should be escaped
     */
    public abstract function escape_param($param);

    protected function compile_expressions($expr_list) {
        foreach ($expr_list as $expr) {
            $compiled_exprs []= $expr->compile_expr($this);
        }
        return implode(' AND ', $compiled_exprs);
    }

}