<?php

class JORK_Mapper_ExpressionTest extends Kohana_Unittest_TestCase {

    /**
     * @expectedException JORK_Exception
     */
    public function testExpression() {
        $jork_query = new JORK_Query_Select;
        $jork_query->select(DB::expr('{user.id} || {user.name} || {user.email} || {user.posts.id}'))->from('Model_User user');
        $mapper = new JORK_Mapper_Select($jork_query);
        list($db_query, ) = $mapper->map();
        $jork_query->select(DB::expr('{user.posts}'));
        $mapper->map();
    }
}