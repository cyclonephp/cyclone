<?php


class JORK {

    const ONE_TO_ONE = 0;

    const ONE_TO_MANY = 1;

    const MANY_TO_MANY = 2;

    public static function select($entity) {
        $query = new JORK_Query_Select;
        $query->entity = $entity;
        return $query;
    }

}