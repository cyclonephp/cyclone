<?php


class SimpleDB_Test extends Kohana_Unittest_TestCase {

    public function testInstance() {
        $inst = DB::inst();
        $this->assertTrue($inst instanceof DB_Adapter_Mysqli);
        $inst->disconnect();
    }

    public function testQueryFactory() {
        $query = DB::select();
        $this->assertEquals($query->columns, array(DB::expr('*')));

        $query = DB::update('user');
        $this->assertEquals($query->table, 'user');

        $query = DB::insert('user');
        $this->assertEquals($query->table, 'user');

        $query = DB::delete('user');
        $this->assertEquals($query->table, 'user');
    }

    public function testExpressionFactory() {
        $expr = DB::expr('a', '=', 'b');
        $this->assertTrue($expr instanceof  DB_Expression_Binary);

        $expr = DB::expr('exists', DB::select());
        $this->assertTrue($expr instanceof  DB_Expression_Unary);
    }

    public function testQuerySelect() {
        $query = DB::select()->from('user')
                ->join('group')->on('user.group_fk', '=', 'group.id')
                ->where('exists', DB::select()->from('user'))
                ->group_by('id', 'name')
                ->having('hello', '=', 'world')
                ->offset(2)
                ->limit(10);
    }

    public function testQueryDelete() {
        $query = DB::delete('user')->where('id', '=', 15);
    }

}