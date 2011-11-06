<?php

namespace cyclone\request;

/**
 * Abstract controller class. Controllers should only be created using a \c Request.
 *
 * Controllers methods will be automatically called in the following order by
 * the request:
 * @code
 *     $controller = new namespace\FooController($request);
 *     $controller->before();
 *     $controller->action_bar();
 *     $controller->after();
 * @endcode
 * 
 * The controller action should add the output it creates to
 * `$this->response`, typically in the form of a View, during the
 * "action" part of execution.
 *
 * @package    cyclone
 * @category   Controller
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class SkeletonController {

	/**
         * The request that the controller was created for.
         *
	 * @var  Request
	 */
	protected $_request;

        /**
         * The response for \c $_request . Same as
         * <code>$this->_request->get_response()</code
         *
         * @var Response
         */
        protected $_response;

        /**
         * Alias for \c $_request
         *
         * @var Request
         */
        protected $_req;

        /**
         * Alias for \c $_response
         *
         * @var Response
         */
        protected $_resp;

	/**
	 * Creates a new controller instance. Each controller must be constructed
	 * with the request object that created it.
	 *
	 * @param   object  Request that created the controller
	 * @return  void
	 */
	public function __construct(Request $request, Response $response)
	{
		// Assign the request to the controller
		$this->_req = $this->_request = $request;
                $this->_resp = $this->_response = $response;
	}

	/**
	 * Automatically executed before the controller action. Can be used to set
	 * class properties, do authorization checks, and execute other custom code.
	 *
	 * @return  void
	 */
	public function before()
	{
		// Nothing by default
	}

	/**
	 * Automatically executed after the controller action. Can be used to apply
	 * transformation to the request response, add extra output, and execute
	 * other custom code.
	 *
	 * @return  void
	 */
	public function after()
	{
		// Nothing by default
	}

} // End Controller
