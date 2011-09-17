<?php

use cyclone as cy;

class Route_Test extends Kohana_Unittest_TestCase {

    public function  setUp() {
        parent::setUp();
        cy\Route::clear();
    }

    public function testMethodMatch() {
        $route = new cy\Route;
        $route->method('GeT');
        $request = new cy\Request;
        $request->method('post');
        $this->assertFalse($route->matches($request));
    }

    public function testIsAjaxMatch() {
        $route = new cy\Route;
        $route->is_ajax(TRUE);
        $request = new cy\Request;
        $this->assertFalse($route->matches($request));
    }

    public function testProtocolMatch() {
        $route = new cy\Route;
        $route->protocol('https');
        $request = new cy\Request;
        $request->protocol('http');
        $this->assertFalse($route->matches($request));
    }

    public function testMatchesURI() {
        $route = new cy\Route('<controller>/<action>');
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
        $route = new cy\Route('(<controller>(/<action>))');
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
        $route = new cy\Route('(<action>)', array(
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
        $route = new cy\Route('(<controller>(/<action>))');
        $route->before(function(&$params) {
           $params['controller'] = 'changed';
        });
        $request = new cy\Request('hello/world');
        $route->matches($request);
        $this->assertEquals(array(
            'controller' => 'changed',
            'action' => 'world'
        ), $request->params);
    }

}