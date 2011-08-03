<?php

class Dispatcher_Internal extends Dispatcher {

    const STRATEGY_DEFAULT = 'default';

    const STRATEGY_LAMBDA = 'lambda';

    public static $default_strategy = self::STRATEGY_DEFAULT;

    public function  dispatch($strategy = NULL) {
        if (NULL === $strategy) {
            $strategy = self::$default_strategy;
        }
        foreach (Route::all() as $route) {
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
                            throw new Dispatcher_Exception('Unknown dispatch strategy: ' . $strategy);
                    }
                } catch (Dispatcher_Exception $ex) {
                    
                } catch (Exception $ex) {

                }
            }
        }
    }

    public function dispatch_default(Route $route) {
        $params = $this->request->params;
        if ( ! isset($params['controller']) || ! isset($params['action']))
            throw new Dispatcher_Exception('The default strategy of Dispatcher_Internal requires the "controller" and "action" route parameters');

        try {
            $action_params = $params;
            unset($action_params['controller'], $action_params['action']);

            $controller_class = new ReflectionClass('controller_' . $params['controller']);

            $controller = $controller_class->newInstance($this->request);
            
            $controller_class->getMethod($action_method = 'action_' . $params['action'])
                    ->invokeArgs($controller, $action_params);

        } catch (ReflectionException $ex) {
            throw new Dispatcher_Exception($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function dispatch_lambda(Route $route) {
        $controller = $route->lambda_controller;
        $controller($this->request);
    }

}

