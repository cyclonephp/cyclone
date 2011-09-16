<?php

namespace cyclone\dispatcher;

use cyclone as cy;

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package cyclone
 */
abstract class AbstractDispatcher {
    
    public static function for_request(cy\Request $req) {
        if (\strpos($req->uri, '://') !== FALSE) // seems to be an absolute URL
            return new ExternalDispatcher($req);
        
        return new InternalDispatcher($req);
    }

    /**
     *
     * @var Request
     */
    public $request;
    
    public final function __construct(cy\Request $request) {
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