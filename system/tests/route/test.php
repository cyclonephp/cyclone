<?php

use cyclone\request as req;

class Route_Test extends Kohana_Unittest_TestCase {

    public function  setUp() {
        parent::setUp();
        req\Route::clear();
    }

    public function testMethodMatch() {
        $route = new req\Route;
        $route->method('GeT');
        $request = new req\Request;
        $request->method('post');
        $this->assertFalse($route->matches($request));
    }

    public function testIsAjaxMatch() {
        $route = new req\Route;
        $route->is_ajax(TRUE);
        $request = new req\Request;
        $this->assertFalse($route->matches($request));
    }

    public function testProtocolMatch() {
        $route = new req\Route;
        $route->protocol('https');
        $request = new req\Request;
        $request->protocol('http');
        $this->assertFalse($route->matches($request));
    }

    public function testMatchesURI() {
        $route = new req\Route('<controller>/<action>');
        $route->defaults(array(
            'namespace' => 'app\controller'
        ));
        $this->assertFalse($route->matches_uri('asdasd'));
        $params = $route->matches_uri('ctrl/act');
        $this->assertEquals(array(
            'controller' => 'ctrl',
            'action' => 'act',
            'namespace' => 'app\controller'
        ), $params);
    }

    public function testOptionalRouteParams() {
        $route = new req\Route('(<controller>(/<action>))');
        $route->defaults(array(
            'controller' => 'def_ctrl',
            'action' => 'def_action'
        ));
        $this->assertEquals(array(
            'controller' => 'hello',
            'action' => 'world'
        ), $route->matches_uri('hello/world'));
        $this->assertEquals(array(
            'controller' => 'hello',
            'action' => 'def_action'
        ), $route->matches_uri('hello'));
        $this->assertEquals(array(
            'controller' => 'def_ctrl',
            'action' => 'def_action'
        ), $route->matches_uri(''));
    }

    public function testRouteRegex() {
        $route = new req\Route('(<action>)', array(
            'action' => '(login|logout)'
        ));
        $route->defaults(array('controller' => 'auth'));
        $this->assertEquals(array(
            'action' => 'login',
            'controller' => 'auth'
        ), $route->matches_uri('login'));
        $this->assertFalse($route->matches_uri('whatever'));
    }

    public function testBeforeCall() {
        $route = new req\Route('(<controller>(/<action>))');
        $route->before(function(&$params) {
           $params['controller'] = 'changed';
        });
        $request = new req\Request('hello/world');
        $route->matches($request);
        $this->assertEquals(array(
            'controller' => 'changed',
            'action' => 'world'
        ), $request->params);
    }

}