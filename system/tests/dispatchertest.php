<?php

use cyclone as cy;
use cyclone\request as req;

class DispatcherTest extends Kohana_Unittest_TestCase {

    public static $beforeCalled = FALSE;

    public static $afterCalled = FALSE;

    public static $actionCalled = FALSE;

    public function  setUp() {
        parent::setUp();
        req\Route::clear();
    }

    /**
     * @expectedException cyclone\request\DispatcherException
     */
    public function testInternalLambda() {
        req\InternalDispatcher::$default_strategy = req\InternalDispatcher::STRATEGY_LAMBDA;
        req\Route::set('lambda-test', 'hello/<name>')
                ->controller(function($req) use (&$name){
            $name = $req->params['name'];
        });
        req\Request::factory('hello/user')->execute();
        $this->assertEquals('user', $name);

        req\Route::set('lambda-fail-test', 'dummy');
        req\Request::factory('dummy')->execute();
    }

    public function testInternalDefault() {
        self::$beforeCalled = self::$afterCalled = self::$actionCalled = FALSE;
        req\InternalDispatcher::$default_strategy = req\InternalDispatcher::STRATEGY_DEFAULT;
        req\Route::set('default-test', '<controller>(/<action>)');
        eval('class TestController extends cyclone\request\SkeletonController {
        function before() {
            DispatcherTest::$beforeCalled = TRUE;
        }

        function after() {
            DispatcherTest::$afterCalled = TRUE;
        }

        function action_act() {
            DispatcherTest::$actionCalled = TRUE;
        }
}');
        req\Request::factory('test/act')->execute();
        $this->assertTrue(self::$beforeCalled, 'before called');
        $this->assertTrue(self::$afterCalled, 'after called');
        $this->assertTrue(self::$actionCalled, 'action called');
        $failingURIs = array('test', 'nonexistentController');
        foreach ($failingURIs as $uri) {
            $failed = FALSE;
            try {
                req\Request::factory('test')->execute();
            } catch (req\DispatcherException $ex) {
                $failed = TRUE;
            }
            $this->assertTrue($failed, 'failure of URI ' . $uri);
        }
    }

}