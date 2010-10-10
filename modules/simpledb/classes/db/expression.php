<?php

interface DB_Expression {

    public function compile_expr(DB_Adapter $adapter);
    
}