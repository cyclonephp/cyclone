<?php

use cyclone as cy;


class ValidationTest extends Unittest_TestCase {

    public function test_basic() {
        cy\I18n::$lang = 'nonexistent';
        $validation = new cy\Validation('');
        $validation->rule('not_empty');
        $this->assertFalse($validation->validate());
        $this->assertEquals('error.not_empty', $validation->errors[0]);

        $validation->data('foo');
        $this->assertTrue($validation());
    }

    public function test_errormsg_val_label_param() {
        $validation = new cy\Validation('');
        $validation->rule('not_empty', NULL, ':label cannot be empty value: ":value"');
        $validation->label('my label');
        $this->assertFalse($validation->validate());
        $this->assertEquals('my label cannot be empty value: ""', $validation->errors[0]);
    }

    public function test_rule_params() {
        $validation = new cy\Validation('aa');
        $validation->rule('min_length', array(3), ':label must be at least :1 characters long');
        $validation->label('name');
        $this->assertFalse($validation->validate());
        $this->assertEquals('name must be at least 3 characters long', $validation->errors[0]);
    }

    public function test_fail_on_first() {
        $validation = new cy\Validation('aaaa', TRUE);
        $validation->rule('min_length', array(8), ':label must be at least :1 characters long')
                ->rule('max_length', array(3), ':label must be at most :1 characters long')
                ->label('pass this!');
        $this->assertFalse($validation->validate());
        $this->assertEquals(1, count($validation->errors));
        $this->assertEquals('pass this! must be at least 8 characters long', $validation->errors[0]);
        $this->assertFalse($validation->fail_on_first(FALSE)->validate());
        $this->assertEquals(2, count($validation->errors));
        $this->assertEquals('pass this! must be at least 8 characters long', $validation->errors[0]);
        $this->assertEquals('pass this! must be at most 3 characters long', $validation->errors[1]);
    }

    public function test_lambda_rule() {
        $validation = new cy\Validation('aaaa', TRUE);
        $validation->rule(function($value) {
            return FALSE;
        }, array(2), 'my error: :label')->label('lambda');
        $this->assertFalse($validation->validate());
        $this->assertEquals(1, count($validation->errors));
        $this->assertEquals('my error: lambda', $validation->errors[0]);
    }

}
