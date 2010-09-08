<?php


function arr2obj(array $arr, $class, $default = null) {
    if (count($arr) == 0)
        return $default;
    $obj = new $class;
    foreach ($arr as $k => $v) {
        $obj->$k = $v;
    }
    return $obj;
}

function matrix2objarr(array $matrix, $class) {
    $rval = array();
    foreach ($matrix as $arr) {
        if ( ! is_array($arr))
            throw new Exception($arr.' is not an array');
        $rval []= arr2obj($arr, $class);
    }
    return $rval;
}

function onerow2obj($matrix, $class, $default = null) {
    switch(count($matrix)) {
        case 0: return $default;
        case 1: return arr2obj($matrix[0], $class, $default);
        default: throw new Exception('unexpected row count in matrix: '.count($matrix));
    }
}
