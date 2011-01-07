<?php


class JORK_Test extends Kohana_Unittest_TestCase {

    public function testHelpers() {
        $this->assertTrue(JORK::select() instanceof JORK_Query_Select);
        $this->assertTrue(JORK::from() instanceof JORK_Query_Select);
    }
}