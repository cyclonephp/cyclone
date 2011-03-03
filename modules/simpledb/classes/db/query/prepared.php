<?php

interface DB_Query_Prepared {

    public function param($key, $value);

    public function params(array $params);

    public function exec();
    
}