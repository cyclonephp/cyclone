<?php


class Record_Test extends Kohana_Unittest_TestCase {

    private $names = array(1 => 'user1', 2 => 'user2');

    public function setUp() {
        try {
            DB::query('truncate cy_user')->exec();
            $names = array('user1', 'user2');
            $insert = DB::insert('user');
            foreach ($names as $name) {
                $insert->values(array('name' => $name));
            }
            $insert->exec();
        } catch (Exception $ex) {
            $this->markTestSkipped('skipping simpledb tests');
        }
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
        $user = Record_User::inst()->get(20);
        $this->assertNull($user);
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

    /**
     * @expectedException Exception
     */
    public function testGetOne() {
        $user = Record_User::inst()->get_one(array('name', '=', DB::esc('user1')));
        $this->assertTrue($user instanceof Record_User);
        $this->assertEquals(1, $user->id);
        $this->assertEquals('user1', $user->name);
        Record_User::inst()->get_one(array('id', 'in', DB::expr(array(1, 2))));
    }

    public function testGetList() {
        $users = Record_User::inst()->get_list(
            array('id', 'in', DB::expr(array(1, 2))),
            array('name', 'desc')
        );
        $this->assertTrue($users instanceof DB_Query_Result);
        $this->assertEquals(2, count($users));
        $users = $users->as_array();
        $this->assertEquals($users[0]->id, 2);
        $this->assertEquals($users[0]->name, 'user2');
    }

    public function testGetAll() {
        $users = Record_User::inst()->get_all();
        $this->assertTrue($users instanceof DB_Query_Result);
        $this->assertEquals(2, count($users));
        $idx = 0;
        foreach ($users as $user) {
            $this->assertEquals($user->name, $this->names[$user->id]);
        }
    }

    public function testGetPage() {
        $users = Record_User::inst()->get_page(2, 1, array('id', 'in'
            , DB::expr(array(1, 2))))->as_array();
        $this->assertEquals(count($users), 1);
        $user = $users[0];
        $this->assertEquals('user2', $user->name);
    }

    /**
     * @expectedException Exception
     */
    public function testDelete() {
        $user = Record_User::inst()->get(1);
        $user->delete();
        Record_User::inst()->delete(2);
        $remaining = DB::select()->from('user')->exec();
        $this->assertEquals(0, count($remaining));
        Record_User::inst()->delete();
    }

    public function testCount() {
        $count = Record_User::inst()->count(array('id', '=', DB::esc(1)));
        $this->assertEquals(1, $count);
    }
}