<?php

use cyclone as cy;

class Core_JSONTest extends Kohana_Unittest_TestCase {

    public function testEncodeScalar() {
        $json = cy\JSON::encode(42);
        $this->assertEquals('42', $json);

        $json = cy\JSON::encode('tststr');
        $this->assertEquals('"tststr"', $json);

        $json = cy\JSON::encode(true);
        $this->assertEquals('true', $json);

        $this->assertEquals('"foo\"bar\'"', cy\JSON::encode("foo\"bar'"));
    }

    public function testEncodeArray() {
        $arr = array(2);
        $this->assertEquals('[2]', cy\JSON::encode($arr));
        $arr []= 5;
        $this->assertEquals('[2,5]', cy\JSON::encode($arr));
        $arr []= 6;
        $this->assertEquals('[2,5,6]', cy\JSON::encode($arr));
    }

    public function testEncodeObject() {
        $arr = array(1 => 1);
        $this->assertEquals('{"1":1}', cy\JSON::encode($arr));
        $arr[2] = true;
        $this->assertEquals('{"1":1,"2":true}', cy\JSON::encode($arr));

        $this->assertEquals('{}', cy\JSON::encode(new stdClass));

        $obj = new stdClass;
        $obj->a = 'a';
        $obj->b = 5.24;
        $this->assertEquals('{"a":"a","b":5.24}', cy\JSON::encode($obj));

        $this->assertEquals('{"foo":"bar"}', cy\JSON::encode(new TestJsonSer));
    }


}

class TestJsonSer {

    public function jsonSerializable() {
        return array('foo' => 'bar');
    }
    
}