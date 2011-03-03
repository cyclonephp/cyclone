<?php

class DB_Compiler_Mysqli extends DB_Compiler_Abstract {

    protected $esc_char = '`';

    public function compile_hints($hints) {
        $rval = " USE";
        foreach ($hints as $hint) {
            $rval .= ' ' . $hint;
        };
        return $rval;
    }

    public function escape_table($table){
        if ($table instanceof DB_Expression)
            return $table->compile_expr($this);

        $prefix = array_key_exists('prefix', $this->_config)
                ? $this->_config['prefix']
                : '';

        if (is_array($table)) {
            list($table_name, $alias) = $table;
            if ($table_name instanceof DB_Expression) {
                $rtable = '(' . $table_name->compile_expr($this) . ') `' . $table[1] . '`';
            } else {
                $rtable = '`' . $prefix . $table[0] . '` `' . $table[1] . '`';
            }
        } else {
            $rtable = '`' . $prefix . $table . '`';
        }

        return $rtable;
    }

    public function escape_param($param) {
        if (NULL === $param)
            return 'NULL';

        return "'".$this->_db_conn->real_escape_string($param)."'"; //TODO test & secure
    }

    public function compile_alias($expr, $alias) {
        return $expr.' AS `'.$alias.'`';
    }
    
}