<?php

namespace cyclone\request;

use cyclone as cy;

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package cyclone
 */
class InternalDispatcher extends AbstractDispatcher {

    const STRATEGY_DEFAULT = 'default';

    const STRATEGY_LAMBDA = 'lambda';

    const STRATEGY_QUERY = 'query';

    public static $query_keys = array(
        'controller' => 'controller',
        'action' => 'action',
        'namespace' => 'namespace'
    );

    public static $default_query_values = array(
        'controller' => 'index',
        'action' => 'index',
        'namespace' => ''
    );

    public static $default_strategy = self::STRATEGY_DEFAULT;

    public function  dispatch($strategy = NULL) {
        if (NULL === $strategy) {
            $strategy = self::$default_strategy;
        }
        if ($strategy == self::STRATEGY_QUERY) {
            $this->dispatch_query();
            return;
        }
        $last_dispatcher_exception = NULL;
        foreach (Route::all() as $route) {
            if ($route->matches($this->request)) {
                try {
                    switch ($strategy) {
                        case self::STRATEGY_DEFAULT:
                            $this->dispatch_default($route);
                            return;
                        case self::STRATEGY_LAMBDA:
                            $this->dispatch_lambda($route);
                            return;
                        default:
                            throw new DispatcherException('Unknown dispatch strategy: ' . $strategy);
                    }
                } catch (DispatcherException $ex) {
                    $last_dispatcher_exception = $ex;
                    continue;
                }
            }
        }
        if ( ! is_null($last_dispatcher_exception))
            // matching route found, but an exception has been thrown by the dispatcher;
            // it can have a more useful message then 'failed to find matching route..'
            throw $last_dispatcher_exception;

        throw new DispatcherException('failed to find matching route for request \''
                . $this->request->uri . '\'');
    }

    public function dispatch_default(Route $route = NULL) {
        $params = $this->request->params;
        if (!(isset($params['controller']) && isset($params['action'])))
            throw new DispatcherException('The default strategy of InternalDispatcher requires the "controller" and "action" route parameters');

        $action_params = $params;

        if (isset($action_params['namespace'])) {
            $controller_classname = $action_params['namespace'] . '\\';
        } else {
            $controller_classname = '';
        }

        $controller_classname .= ucfirst($params['controller']) . 'Controller';

        $action_name = $params['action'];

        $this->exec_request($controller_classname, $action_name);
    }

    public function dispatch_lambda(Route $route) {
        $controller = $route->lambda_controller;
        if ( ! is_callable($controller))
            throw new DispatcherException('failed to dispatch request by default strategy: the matching route does not have a lambda controller');

        $controller($this->request);
    }

    protected function exec_request($controller_classname, $action_name) {
        if (!\class_exists($controller_classname))
            throw new DispatcherException("Class '$controller_classname' does not exist.");

        try {
            $controller = new $controller_classname($this->request
                            , $this->request->get_response()
            );

            if (!$controller instanceof \cyclone\request\SkeletonController)
                throw new DispatcherException("controller class '$controller_classname' is not a subclass of cyclone\controller\SkeletonController");

            $action_name = 'action_' . $action_name;
            
            if (!method_exists($controller, $action_name))
                throw new DispatcherException("Action $controller_classname::$action_name() does not exist");

            $controller->before();

            $controller->$action_name();

            $controller->after();
        } catch (\ReflectionException $ex) {
            throw new DispatcherException('failed to dispatch request by default strategy: '
                    . $ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function dispatch_query() {
        $request = $this->request;
        $query_params = $request->query;

        $ns_key = self::$query_keys['namespace'];
        if ( ! isset($query_params[$ns_key])) {
            $query_params[$ns_key] = $request->query[$ns_key] = self::$default_query_values['namespace'];
        }
        $controller_classname = $query_params[$ns_key];
        if ($controller_classname != '') {
            $controller_classname .= '\\';
        }
        $ctrl_key = self::$query_keys['controller'];
        $controller_classname .= ucfirst($query_params[$ctrl_key] ?: self::$default_query_values['controller']);
        $controller_classname .= 'Controller';

        $action_key = self::$query_keys['action'];
        $action_name = $query_params[$action_key] ?: self::$default_query_values['action'];
        $this->exec_request($controller_classname, $action_name);
    }

}

