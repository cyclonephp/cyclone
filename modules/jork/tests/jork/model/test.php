<?php


class JORK_Model_Test extends Kohana_Unittest_TestCase {

    public function testInst() {
        Model_User::inst();
    }
}