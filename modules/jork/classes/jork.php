<?php


class JORK {

    public static function select($entity) {
        $query = new JORK_Query_Select;
        $query->entity = $entity;
        return $query;
    }

}