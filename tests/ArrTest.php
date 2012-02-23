<?php

use cyclone as cy;

class ArrTest extends Kohana_Unittest_TestCase {

    public function providerTestDiff() {
        return array(
            array(
                array(
                    array(1),
                    array(2),
                    array(1, 3)
                ),
                array()
            ),
            array(
                array(
                    array(1),
                    array(2),
                    array(3)
                ),
                array(1)
            )
        );
    }

    /**
     *
     * @param array $arrays
     * @param array $diff
     * @dataProvider providerTestDiff
     */
    public function testDiff($arrays, $diff) {
        $actual = call_user_func_array('cyclone\\Arr::diff', $arrays);
        $this->assertEquals($diff, $actual);
    }

}