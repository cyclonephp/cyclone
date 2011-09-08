<?php

namespace cyclone;

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package cyclone
 */
abstract class Dispatcher {
    
    public static function for_request(Request $req) {
        if (strpos($req->uri, '://') !== FALSE) // seems to be an absolute URL
            return new Dispatcher_External($req);
        
        return new Dispatcher_Internal($req);
    }

    /**
     *
     * @var Request
     */
    public $request;
    
    public final function __construct(Request $request) {
        $this->request = $request;
    }
    
    /**
     * Dispatches the request. The possible values of \c $strategy
     * are implementation-specific.
     * 
     * @param string $strategy
     */
    public abstract function dispatch($strategy = NULL);
}