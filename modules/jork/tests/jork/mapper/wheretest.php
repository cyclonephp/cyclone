<?php


class JORK_Mapper_WhereTest extends Kohana_Unittest_TestCase {

    public function testWhereImpl() {
        $jork_query = JORK::from('Model_User')
                ->where('posts.created_at', '>', DB::expr('2010-11-11'))
                ->where('exists', 'name')
                ->where('avg({id}) > x');
        $mapper = new JORK_Mapper_Select($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->where_conditions, array(
            new DB_Expression_Binary('t_posts_0.created_at', '>'
                    , DB::expr('2010-11-11')),
            new DB_Expression_Unary('exists', 't_users_0.name'),
            new DB_Expression_Custom('avg(t_users_0.id) > x')
        ));
    }

    public function testWhere() {
        $jork_query = JORK::from('Model_User user')
                ->where('user.posts.created_at', '>', DB::expr('2010-11-11'))
                ->where('exists', 'user.name')
                ->where('avg({user.id}) > x');
        $mapper = new JORK_Mapper_Select($jork_query);
        list($db_query, ) = $mapper->map();
        $this->assertEquals($db_query->where_conditions, array(
            new DB_Expression_Binary('t_posts_0.created_at', '>', DB::expr('2010-11-11')),
            new DB_Expression_Unary('exists', 't_users_0.name'),
            new DB_Expression_Custom('avg(t_users_0.id) > x')
        ));
    }

    public function testWhereObj() {
        $jork_query = JORK::from('Model_Post post')
            ->where('post.author', '=', 'post.topic.creator');
        $mapper = new JORK_Mapper_Select($jork_query);
        list($db_query, ) = $mapper->map();
    }
}