<?php


class SimpleDB_Test extends SimpleDB_Mysqli_DbTest {

    public function testPools() {
        $this->assertInstanceOf('DB_Compiler', DB::compiler());
        $this->assertInstanceOf('DB_Executor', DB::executor());
        $this->assertInstanceOf('DB_Connector', DB::connector());
        DB::connector()->disconnect();
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

    public function testConnector() {
        $conn = DB::connector();
        $this->assertInstanceOf('DB_Connector_Mysqli', $conn);
    }

    public function testCompiler() {
        $comp = DB::compiler();
        $this->assertInstanceOf('DB_Compiler_Mysqli', $comp);
    }

    public function testExecutor() {
        $exec = DB::executor();
        $this->assertInstanceOf('DB_Executor_Mysqli', $exec);
    }

    public function testExecutorPrepared() {
        $exec_prep = DB::executor_prepared();
        $this->assertInstanceOf('DB_Executor_Prepared_Mysqli', $exec_prep);
    }

}