<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package SimpleDB
 */
interface DB_Executor_Prepared {

    public function prepare($sql);

    public function exec_select($prepared_stmt, array $params
            , DB_Query_Select $orig_query);

    public function exec_insert($prepared_stmt, array $params);

    public function exec_update($prepared_stmt, array $params);

    public function exec_delete($prepared_stmt, array $params);

}
