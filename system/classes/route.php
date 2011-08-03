<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Routes are used to determine the controller and action for a requested URI.
 * Every route generates a regular expression which is used to match a URI
 * and a route. Routes may also contain keys which can be used to set the
 * controller, action, and parameters.
 *
 * Each <key> will be translated to a regular expression using a default
 * regular expression pattern. You can override the default pattern by providing
 * a pattern for the key:
 *
 *     // This route will only match when <id> is a digit
 *     Route::set('user', 'user/<action>/<id>', array('id' => '\d+'));
 *
 *     // This route will match when <path> is anything
 *     Route::set('file', '<path>', array('path' => '.*'));
 *
 * It is also possible to create optional segments by using parentheses in
 * the URI definition:
 *
 *     // This is the standard default route, and no keys are required
 *     Route::set('default', '(<controller>(/<action>(/<id>)))');
 *
 *     // This route only requires the <file> key
 *     Route::set('file', '(<path>/)<file>(.<format>)', array('path' => '.*', 'format' => '\w+'));
 *
 * Routes also provide a way to generate URIs (called "reverse routing"), which
 * makes them an extremely powerful and flexible way to generate internal links.
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Route {

	// Defines the pattern of a <segment>
	const REGEX_KEY     = '<([a-zA-Z0-9_]++)>';

	// What can be part of a <segment> value
	const REGEX_SEGMENT = '[^/.,;?\n]++';

	// What must be escaped in the route regex
	const REGEX_ESCAPE  = '[.\\+*?[^\\]${}=!|]';

	/**
	 * @var  string  default action for all routes
	 */
	public static $default_action = 'index';

	// List of route objects
	protected static $_routes = array();

	/**
	 * Stores a named route and returns it. The "action" will always be set to
	 * "index" if it is not defined.
	 *
	 *     Route::set('default', '(<controller>(/<action>(/<id>)))')
	 *         ->defaults(array(
	 *             'controller' => 'welcome',
	 *         ));
	 *
	 * @param   string   route name
	 * @param   string   URI pattern
	 * @param   array    regex patterns for route keys
	 * @return  Route
	 */
	public static function set($name, $uri, array $regex = NULL)
	{
		return Route::$_routes[$name] = new Route($uri, $regex);
	}

	/**
	 * Retrieves a named route.
	 *
	 *     $route = Route::get('default');
	 *
	 * @param   string  route name
	 * @return  Route
	 * @throws  Kohana_Exception
	 */
	public static function get($name)
	{
		if ( ! isset(Route::$_routes[$name]))
		{
			throw new Kohana_Exception('The requested route does not exist: :route',
				array(':route' => $name));
		}

		return Route::$_routes[$name];
	}

	/**
	 * Retrieves all named routes.
	 *
	 *     $routes = Route::all();
	 *
	 * @return  array  routes by name
	 */
	public static function all()
	{
		return Route::$_routes;
	}

	/**
	 * Get the name of a route.
	 *
	 *     $name = Route::name($route)
	 *
	 * @param   object  Route instance
	 * @return  string
	 */
	public static function name(Route $route)
	{
		return array_search($route, Route::$_routes);
	}

	/**
	 * Saves or loads the route cache. If your routes will remain the same for
	 * a long period of time, use this to reload the routes from the cache
	 * rather than redefining them on every page load.
	 *
	 *     if ( ! Route::cache())
	 *     {
	 *         // Set routes here
	 *         Route::cache(TRUE);
	 *     }
	 *
	 * @param   boolean   cache the current routes
	 * @return  void      when saving routes
	 * @return  boolean   when loading routes
	 * @uses    Kohana::cache
	 */
	public static function cache($save = FALSE)
	{
		if ($save === TRUE)
		{
			// Cache all defined routes
			Kohana::cache('Route::cache()', Route::$_routes);
		}
		else
		{
			if ($routes = Kohana::cache('Route::cache()'))
			{
				Route::$_routes = $routes;

				// Routes were cached
				return TRUE;
			}
			else
			{
				// Routes were not cached
				return FALSE;
			}
		}
	}

	/**
	 * Create a URL from a route name. This is a shortcut for:
	 *
	 *     echo URL::site(Route::get($name)->uri($params), $protocol);
	 *
	 * @param   string   route name
	 * @param   array    URI parameters
	 * @param   mixed   protocol string or boolean, adds protocol and domain
	 * @return  string
	 * @since   3.0.7
	 * @uses    URL::site
	 */
	public static function url($name, array $params = NULL, $protocol = NULL)
	{
		// Create a URI with the route and convert it to a URL
		return URL::site(Route::get($name)->uri($params), $protocol);
	}

	// Route URI string
	protected $_uri = '';

	// Regular expressions for route keys
	protected $_regex = array();

	// Default values for route keys
	protected $_defaults = array('action' => 'index');

        /**
         * Ajax constraint. If it's value is not null then the route will match
         * only if the requests $is_ajax attribute is the same as this value.
         *
         * @var boolean
         */
        protected $_is_ajax;

        /**
         * HTTP method constraint. If it's value is not null then the route will match
         * only if the requests $method attribute is the same as this value (case
         * insensitively).
         *
         * @var string
         */
        protected $_method;
        
        /**
         * Protocol constraint. If it's value is not null then the route will
         * match only if the request's \c $protocol attribute is the same as
         * this value. Note that you can set programmatically any protocols
         * for the request using the \c Request::protocol() method, but for the
         * initial request (created by \c Request::initial() ) the protocol will
         * always be \c 'http' or \c 'https' .
         *
         * @var string
         */
        protected $_protocol;

	// Compiled regex cache
	protected $_route_regex;

        /**
         * A callback - typically a lambda function - that will be called by
         * \c Dispatcher_Internal if this lambda controller is not null, and
         * the request is dispatched using the \c Dispatcher_Internal::STRATEGY_LAMBDA
         * strategy. The callback should take the assoc. array of parameters
         * extracted by the matching \c Route (from the request URI).
         *
         * @var callback
         */
        protected $_lambda_controller;

        /**
         * A callback that can be used to modify the route parameters before the
         * the controller execution. It is executed after finding the matching
         * route and before calling the controller. It takes the routing parameters
         * as a parameter (assoc. array). The parameters are passed by reference,
         * so the callback can modify the routing parameters by modifying it's parameter.
         * Example:
         * \code
         * Route::set('default', '(<controller>(/<action>(/<id>)))')
         *      ->defaults(array(
         *          'controller' => 'index',
         *          'action' => 'index'
         *      ))
         *      ->before(function (&$params) {
         *          $params['controller'] = str_replace('-', '_', $params['controller']);
         *          $params['action'] = str_replace('-', '_', $params['action']);
         *      });
         * \endcode
         *
         * @var callback
         */
        protected $_before;

	/**
	 * Creates a new route. Sets the URI and regular expressions for keys.
	 * Routes should always be created with [Route::set] or they will not
	 * be properly stored.
	 *
	 *     $route = new Route($uri, $regex);
	 *
	 * @param   string   route URI pattern
	 * @param   array    key patterns
	 * @return  void
	 * @uses    Route::_compile
	 */
	public function __construct($uri = NULL, array $regex = NULL)
	{
		if ($uri === NULL)
		{
			// Assume the route is from cache
			return;
		}

		if ( ! empty($regex))
		{
			$this->_regex = $regex;
		}

		// Store the URI that this route will match
		$this->_uri = $uri;

		// Store the compiled regex locally
		$this->_route_regex = $this->_compile();
	}

	/**
	 * Provides default values for keys when they are not present. The default
	 * action will always be "index" unless it is overloaded here.
	 *
	 *     $route->defaults(array(
	 *         'controller' => 'welcome',
	 *         'action'     => 'index'
	 *     ));
	 *
	 * @param   array  key values
	 * @return  $this
	 */
	public function defaults(array $defaults = NULL)
	{
		$this->_defaults = $defaults;

		return $this;
	}

        /**
         * The setter of the \c $is_ajax property
         *
         * @param boolean $is_ajax
         * @return Route
         */
        public function is_ajax($is_ajax) {
            $this->_is_ajax = $is_ajax;
            return $this;
        }

        /**
         * The setter of the \c $method property.
         *
         * @param string $method
         * @return Route
         */
        public function method($method) {
            $this->_method = $method;
            return $this;
        }
        
        /**
         * The setter of the lambda controller of the route. This callback will
         * be invoked if a request matches this route, and is dispatched by the
         * internal request, using the \c Dispatcher_Internal::STRATEGY_LAMBA
         * strategy. The callback must accept two parameters: a Request and a 
         * Response instance.
         * 
         * Example:
         * @code
         * Route::set('hello/<name>')
         *  ->method('get')
         *  ->controller(function(Request $req, Response $resp) {
         *      $resp->body = 'Hello ' . $req->params['name'];
         *  });
         * @endcode
         * 
         * Otherwise this controller has no effect.
         * 
         * @param callback $lambda_controller
         * @return Route $this
         */
        public function controller($lambda_controller) {
            $this->_lambda_controller = $lambda_controller;
            return $this;
        }

        public function __get($key) {
            static $readonly_attributes = array(
                'lambda_controller',
            );
            if (in_array($key, $readonly_attributes))
                return $this->{'_' . $key};
            return parent::__get();
        }

	/**
	 * Tests if the route matches a given URI. A successful match will return
	 * all of the routed parameters as an array. A failed match will return
	 * boolean FALSE.
	 *
	 *     // Params: controller = users, action = edit, id = 10
	 *     $params = $route->matches('users/edit/10');
	 *
	 * This method should almost always be used within an if/else block:
	 *
	 *     if ($params = $route->matches($uri))
	 *     {
	 *         // Parse the parameters
	 *     }
	 *
	 * @param   string  URI to match
	 * @return  array   on success
	 * @return  FALSE   on failure
	 */
	public function matches_uri($uri)
	{
		if ( ! preg_match($this->_route_regex, $uri, $matches))
			return FALSE;

		$params = array();
		foreach ($matches as $key => $value)
		{
			if (is_int($key))
			{
				// Skip all unnamed keys
				continue;
			}

			// Set the value for all matched keys
			$params[$key] = $value;
		}

		foreach ($this->_defaults as $key => $value)
		{
			if ( ! isset($params[$key]) OR $params[$key] === '')
			{
				// Set default values for any key that was not matched
				$params[$key] = $value;
			}
		}

		return $params;
	}

        /**
         * Checks if the \& $request matches the route constraints, including
         * - the URI pattern
         * - \c $is_ajax
         * - \c $method
         * - \c $protocol
         * 
         * If matches, then it passes the route pattern parameters to the request
         * and the lambda controller if exists.
         *
         * @param Request $request the request instance to match against
         * @return boolean FALSE if the route doesn't match the request. Otherwise
         *      the parameters extracted from the URI
         */
        public function matches(Request $request) {
            if ( ! is_null($this->_method) 
                && strtolower($request->method) != strtolower($this->_method))
                    return FALSE;
            
            if ( ! is_null($this->_is_ajax) && $request->is_ajax !== $this->_is_ajax)
                    return FALSE;
            
            if ( ! is_null($this->_protocol) 
                && strtolower($request->protocol) != strtolower($this->_protocol))
                    return FALSE;
            
            $params = $this->matches_uri($request->uri);
            
            if (FALSE === $params)
                return FALSE;
            
            $params += $this->_defaults;

            if ( ! is_null($this->_before)) {
                
                if ( ! is_callable($this->_before))
                     throw new Dispatcher_Exception('Route::$before must be a callable');

                $before = $this->_before;
                $before($params);
            }

            $request->params($params);
            
            if ( ! is_null($this->_lambda_controller)) {
                $request->lambda_controller($this->_lambda_controller);
            }
            
            return TRUE;
        }

	/**
	 * Generates a URI for the current route based on the parameters given.
	 *
	 *     // Using the "default" route: "users/profile/10"
	 *     $route->uri(array(
	 *         'controller' => 'users',
	 *         'action'     => 'profile',
	 *         'id'         => '10'
	 *     ));
	 *
	 * @param   array   URI parameters
	 * @return  string
	 * @throws  Kohana_Exception
	 * @uses    Route::REGEX_Key
	 */
	public function uri(array $params = NULL)
	{
		if ($params === NULL)
		{
			// Use the default parameters
			$params = $this->_defaults;
		}
		else
		{
			// Add the default parameters
			$params += $this->_defaults;
		}

		// Start with the routed URI
		$uri = $this->_uri;

		if (strpos($uri, '<') === FALSE AND strpos($uri, '(') === FALSE)
		{
			// This is a static route, no need to replace anything
			return $uri;
		}

		while (preg_match('#\([^()]++\)#', $uri, $match))
		{
			// Search for the matched value
			$search = $match[0];

			// Remove the parenthesis from the match as the replace
			$replace = substr($match[0], 1, -1);

			while(preg_match('#'.Route::REGEX_KEY.'#', $replace, $match))
			{
				list($key, $param) = $match;

				if (isset($params[$param]))
				{
					// Replace the key with the parameter value
					$replace = str_replace($key, $params[$param], $replace);
				}
				else
				{
					// This group has missing parameters
					$replace = '';
					break;
				}
			}

			// Replace the group in the URI
			$uri = str_replace($search, $replace, $uri);
		}

		while(preg_match('#'.Route::REGEX_KEY.'#', $uri, $match))
		{
			list($key, $param) = $match;

			if ( ! isset($params[$param]))
			{
				// Ungrouped parameters are required
				throw new Kohana_Exception('Required route parameter not passed: :param',
					array(':param' => $param));
			}

			$uri = str_replace($key, $params[$param], $uri);
		}

		// Trim all extra slashes from the URI
		$uri = preg_replace('#//+#', '/', rtrim($uri, '/'));

		return $uri;
	}

	/**
	 * Returns the compiled regular expression for the route. This translates
	 * keys and optional groups to a proper PCRE regular expression.
	 *
	 *     $regex = $route->_compile();
	 *
	 * @return  string
	 * @uses    Route::REGEX_ESCAPE
	 * @uses    Route::REGEX_SEGMENT
	 */
	protected function _compile()
	{
		// The URI should be considered literal except for keys and optional parts
		// Escape everything preg_quote would escape except for : ( ) < >
		$regex = preg_replace('#'.Route::REGEX_ESCAPE.'#', '\\\\$0', $this->_uri);

		if (strpos($regex, '(') !== FALSE)
		{
			// Make optional parts of the URI non-capturing and optional
			$regex = str_replace(array('(', ')'), array('(?:', ')?'), $regex);
		}

		// Insert default regex for keys
		$regex = str_replace(array('<', '>'), array('(?P<', '>'.Route::REGEX_SEGMENT.')'), $regex);

		if ( ! empty($this->_regex))
		{
			$search = $replace = array();
			foreach ($this->_regex as $key => $value)
			{
				$search[]  = "<$key>".Route::REGEX_SEGMENT;
				$replace[] = "<$key>$value";
			}

			// Replace the default regex with the user-specified regex
			$regex = str_replace($search, $replace, $regex);
		}

		return '#^'.$regex.'$#uD';
	}

} // End Route
