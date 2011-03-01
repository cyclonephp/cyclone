<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
interface DB_Expression {

    public function compile_expr(DB_Adapter $adapter);
    
}
