<?php


class JORK {

    const ONE_TO_ONE = 0;

    const ONE_TO_MANY = 1;

    const MANY_TO_MANY = 2;

    const MANY_TO_ONE = 3;

    private static $_instance;

    public static function inst() {
        if (null === self::$_instance) {
            self::$_instance = new JORK;
        }
        return self::$_instance;
    }

    private function  __construct() {
        //empty private constructor
    }

    /**
     *
     * @param JORK_Query_Select $jork_query
     * @return JORK_Executor_Select
     */
    public function map_select(JORK_Query_Select $jork_query) {
        
    }


}