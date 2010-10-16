<?php


class Record_Test extends Kohana_Unittest_TestCase {

    public function setUp() {
        DB::query('truncate user')->exec();
        $names = array(1 => 'user1', 2 => 'user2');
        $insert = DB::insert('user');
        foreach ($names as $id => $name) {
            $insert->values(array('id' => $id, 'name' => $name));
        }
        $insert->exec();
    }

    public function tearDown() {
        DB::clear_connections();
    }


    public function testGet() {
        $user = Record_User::inst();
        $user = $user->get(1);
        $this->assertTrue($user instanceof Record_User);
        $this->assertEquals(1, $user->id);
        $this->assertEquals('user1', $user->name);
    }

    public function testSave() {
        $user = new Record_User;
        $user->name = 'user3';
        $user->save();
        $this->assertEquals(3, $user->id);

        $row = DB::select()->from('user')->where('id', '=', DB::esc(3))->exec()->as_array();
        $this->assertEquals($row[0], array('id' => 3, 'name' => 'user3', 'email' => null));

        $user2 = Record_User::inst()->get(2);
        $user2->name = 'user2_';
        $user2->save();
    }

    public function testGetOne() {
        $user = Record_User::inst()->get_one(array('name', '=', DB::esc('user1')));
        $this->assertTrue($user instanceof Record_User);
        $this->assertEquals(1, $user->id);
        $this->assertEquals('user1', $user->name);
    }

    public function testGetList() {
        $users = Record_User::inst()->get_list(
            array('id', 'in', DB::expr(array(1, 2))),
            array('name', 'desc')
        );
        $this->assertTrue($users instanceof DB_Query_Result);
        $users = $users->as_array();
        $this->assertEquals($users[0]->id, 2);
        $this->assertEquals($users[0]->name, 'user2');
    }
}