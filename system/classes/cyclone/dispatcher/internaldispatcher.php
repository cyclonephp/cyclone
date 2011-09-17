<?php

namespace cyclone\dispatcher;

use cyclone as cy;

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package cyclone
 */
class InternalDispatcher extends AbstractDispatcher {

    const STRATEGY_DEFAULT = 'default';

    const STRATEGY_LAMBDA = 'lambda';

    public static $default_strategy = self::STRATEGY_DEFAULT;

    public function  dispatch($strategy = NULL) {
        if (NULL === $strategy) {
            $strategy = self::$default_strategy;
        }
        foreach (cy\Route::all() as $route) {
            if (($params = $route->matches($this->request)) !== FALSE) {
                $this->request->params($params);
                try {
                    switch ($strategy) {
                        case self::STRATEGY_DEFAULT:
                            $this->dispatch_default($route);
                            break;
                        case self::STRATEGY_LAMBDA:
                            $this->dispatch_lambda($route);
                            break;
                        default:
                            throw new Exception('Unknown dispatch strategy: ' . $strategy);
                    }
                } catch (Exception $ex) {
                    
                } catch (\Exception $ex) {

                }
            }
        }
    }

    public function dispatch_default(cy\Route $route) {
        $params = $this->request->params;
        if ( ! (isset($params['controller']) && isset($params['action'])))
            throw new Exception('The default strategy of InternalDispatcher requires the "controller" and "action" route parameters');

        try {
            $action_params = $params;
            unset($action_params['controller'], $action_params['action']);

            if (isset($action_params['namespace'])) {
                $controller_classname = $action_params['namespace'] . '\\';
                unset($action_params['namespace']);
            } else {
                $controller_classname = '';
            }

            $controller_classname .= $params['controller'] . 'Controller';

            $controller_class = new \ReflectionClass($controller_class);

            $controller = $controller_class->newInstance($this->request);
            
            $controller_class->getMethod($action_method = 'action_' . $params['action'])
                    ->invokeArgs($controller, $action_params);

        } catch (\ReflectionException $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function dispatch_lambda(cy\Route $route) {
        $controller = $route->lambda_controller;
        $controller($this->request);
    }

}

