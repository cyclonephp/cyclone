<?php

/**
 * Adapter interface.
 *
 * An adapter is responsible for mapping JORK queries to SimpleDB queries.
 *
 * 
 */
interface JORK_Adapter {

    /**
     * @return JORK_Query_Result
     */
    public function exec_select(JORK_Query_Select $select);
    
}