<?php

use cyclone as cy;
use cyclone\request as req;

class DispatcherTest extends Kohana_Unittest_TestCase {

    public static $beforeCalled = FALSE;

    public static $afterCalled = FALSE;

    public static $actionCalled = FALSE;

    public static $route;

    public function  setUp() {
        parent::setUp();
        req\Route::clear();
        if ( ! class_exists('TestController')) {
            $this->create_mock_controller();
        }
    }

    /**
     * @expectedException cyclone\request\DispatcherException
     */
    public function test_internal_lambda() {
        req\InternalDispatcher::$default_strategy = req\InternalDispatcher::STRATEGY_LAMBDA;

        req\Route::set('lambda-test', 'hello/<name>')
                ->controller(function($req) use (&$name, &$route){
            $name = $req->params['name'];
            $route = $req->route;
        });
        req\Request::factory('hello/user')->execute();
        $this->assertEquals('user', $name);
        $this->assertEquals(req\Route::get('lambda-test'), $route);

        req\Route::set('lambda-fail-test', 'dummy');
        req\Request::factory('dummy')->execute();
    }

    public function test_internal_default() {
        self::$beforeCalled = self::$afterCalled = self::$actionCalled = FALSE;
        req\InternalDispatcher::$default_strategy = req\InternalDispatcher::STRATEGY_DEFAULT;
        req\Route::set('default-test', '<controller>(/<action>)');
        
        req\Request::factory('test/act')->execute();
        $this->assertTrue(self::$beforeCalled, 'before called');
        $this->assertTrue(self::$afterCalled, 'after called');
        $this->assertTrue(self::$actionCalled, 'action called');
        $this->assertEquals(req\Route::get('default-test'), self::$route);
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

    public function test_internal_query() {
        self::$beforeCalled = self::$actionCalled = self::$afterCalled = FALSE;
        req\InternalDispatcher::$default_strategy = req\InternalDispatcher::STRATEGY_QUERY;
        req\Request::factory('/')->query(array(
            'controller' => 'test',
            'action' => 'act'
        ))->execute();
        $this->assertTrue(self::$beforeCalled, 'before called');
        $this->assertTrue(self::$afterCalled, 'after called');
        $this->assertTrue(self::$actionCalled, 'action called');
    }

    private function create_mock_controller() {
        // i'm really sorry about this =)
        eval('class TestController extends cyclone\request\SkeletonController {
        function before() {
            DispatcherTest::$beforeCalled = TRUE;
            DispatcherTest::$route = $this->_request->route;
        }

        function after() {
            DispatcherTest::$afterCalled = TRUE;
        }

        function action_act() {
            DispatcherTest::$actionCalled = TRUE;
        }
}');
    }

}