<?php
/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
class DB_Expression_Set implements DB_Expression {

    protected $arr;

    public function  __construct($arr) {
        $this->arr = $arr;
    }


    public function  compile_expr(DB_Compiler $adapter) {
        $escaped_items = array();
        foreach ($this->arr as $itm) {
            $escaped_items []= $adapter->escape_param($itm);
        }
        return '('.implode(', ', $escaped_items).')';
    }

    public function  contains_table_name($table_name) {
        return FALSE;
    }
}
