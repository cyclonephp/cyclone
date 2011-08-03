<?php
require_once MODPATH.'simpledb/tests/simpledb/postgres/dbtest.php';

class SimpleDB_Postgres_CompileTest extends SimpleDB_Postgres_DbTest {

    public function testConnection() {
        DB::connector('postgres')->connect();
    }

    public function testCompileInsert() {
        $query = DB::insert('user')->values(array(
            'name' => 'user'
            , 'email' => 'user@example.com'));
        $this->assertEquals('INSERT INTO "user" ("name", "email") VALUES (\'user\', \'user@example.com\')'
                , $query->compile('postgres'));
    }

    public function testCompileUpdate() {
        $query = DB::update('user')->values(array('name' => NULL, 'email' => 'ebence88@gmail.com'))
                ->where('id', '=', DB::esc(1))->limit(10);

        $this->assertEquals('UPDATE "user" SET "name" = NULL, "email" = \'ebence88@gmail.com\' WHERE "id" = \'1\' LIMIT 10',
                $query->compile('postgres'));
    }

    public function testCompileDelete() {
        $query = DB::delete('user')->where('name', 'like', '%crys%')->limit(10);

        $this->assertEquals('DELETE FROM "user" WHERE "name" like "%crys%" LIMIT 10'
                , $query->compile('postgres'));
    }

    /**
     * @expectedException DB_Exception
     */
    public function testCompileHint() {
        $query = DB::select()->hint('dummy')->from('table');
        $query->compile('postgres');
    }
    
}