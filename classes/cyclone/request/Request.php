<?php

namespace cyclone\request;

use cyclone as cy;

/**
 * Represents a request in the HMVC hierarchy.
 *
 * @property-read string $uri
 * @property-read array $query
 * @property-read array $post
 * @property-read boolean $is_ajax
 * @property-read string $method
 * @property-read array $params
 * @property-read array $cookies
 * @package cyclone
 */
class Request {

    const METHOD_GET = 'GET';

    const METHOD_POST = 'POST';

    const METHOD_PUT = 'PUT';

    const METHOD_DELETE = 'DELETE';

    const METHOD_HEAD = 'HEAD';

    const METHOD_TRACE = 'TRACE';

    const METHOD_CONNECT = 'CONNECT';

    const METHOD_OPTIONS = 'OPTIONS';

    /**
     * Creates the initial request based on the incoming uri. This request
     * instance will be on the top of the HMVC hierarchy.
     *
     * Most of the sources of this method have been copied from Kohana.
     *
     *
     * @return Request
     */
    public static function initial() {
        if ( ! empty($_SERVER['PATH_INFO'])) {
            $uri = $_SERVER['PATH_INFO'];
        } else {
            if (isset($_SERVER['REQUEST_URI'])) {
                $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                $uri = rawurldecode($uri);
            } elseif (isset($_SERVER['PHP_SELF'])) {
                $uri = $_SERVER['PHP_SELF'];
            } elseif (isset($_SERVER['REDIRECT_URL'])) {
                $uri = $_SERVER['REDIRECT_URL'];
            } else
                // If you ever see this error, please report an issue at http://dev.kohanaphp.com/projects/kohana3/issues
                // along with any relevant information about your web server setup. Thanks!
                throw new Kohana_Exception('Unable to detect the URI using PATH_INFO, REQUEST_URI, or PHP_SELF');

            // Get the path from the base URL, including the index file
            $base_url = parse_url(cy\Kohana::$base_url, PHP_URL_PATH);

            if (strpos($uri, $base_url) === 0) {
                // Remove the base URL from the URI
                $uri = substr($uri, strlen($base_url));
            }

            if (cy\Kohana::$index_file AND strpos($uri, cy\Kohana::$index_file) === 0) {
                // Remove the index file from the URI
                $uri = substr($uri, strlen(cy\Kohana::$index_file));
            }
        }

        // Reduce multiple slashes to a single slash
        $uri = preg_replace('#//+#', '/', $uri);

        // Remove all dot-paths from the URI, they are not valid
        $uri = preg_replace('#\.[\s./]*/#', '', $uri);

        $rval = new Request($uri);

        if (isset($_SERVER['REQUEST_METHOD'])) {
            $method = $rval->_method = $_SERVER['REQUEST_METHOD'];
        }

        if ( ! isset($method) || $method !== 'GET' && $method !== 'POST') {
            // Methods besides GET and POST do not properly parse the form-encoded
            // query string into the $_POST array, so we overload it manually.
            parse_str(file_get_contents('php://input'), $_POST);
        }

        $rval->_query = $_GET;
        $rval->_post = $_POST;

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            // This request is an AJAX request
            $rval->_is_ajax = TRUE;
        }

        if ( ! empty($_SERVER['HTTPS'])
                && filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN)) {
            $rval->_protocol = 'https';
        }

        if (isset($_SERVER['HTTP_REFERER'])) {
            $rval->_referrer = $_SERVER['referrer'];
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $rval->_user_agent = $_SERVER['HTTP_USER_AGENT'];
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Use the forwarded IP address, typically set when the
            // client is using a proxy server.
            $rval->_client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            // Use the forwarded IP address, typically set when the
            // client is using a proxy server.
            $rval->_client_ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $rval->_client_ip = $_SERVER['REMOTE_ADDR'];
        }

        return $rval;
    }

    /**
     * The request that is currently executed.
     *
     * @var Request
     */
    public static $current;

    protected static $_stack;

    public static function notify_execution_start(Request $request) {
        array_push(self::$_stack, self::$current = $request);
    }

    /**
     * @return Request the just finished request
     */
    public static function notify_execution_finish() {
        $stack = &self::$_stack;
        $rval = array_pop($stack);
        self::$current = $stack[count($stack) - 1];
        return $rval;
    }

    public static function factory($uri) {
        return new Request($uri);
    }

    /**
     * The request uri
     *
     * @var string
     */
    protected $_uri;

    /**
     * Contains the query parameters. For the initial request it is the same
     * as $_GET
     *
     * @var array
     */
    protected $_query;

    /**
     * Contains the submitted POSTDATA. For the initial request it is the same
     * as $_POST
     *
     * @var array
     */
    protected $_post;

    /**
     * The method of the HTTP request.
     *
     * @var string
     */
    protected $_method;

    /**
     * TRUE if it's an ajax request
     *
     * @var boolean
     */
    protected $_is_ajax;

    /**
     * The protocol of the request.
     *
     * @var string
     */
    protected $_protocol = 'http';

    /**
     * The referrer URL.
     *
     * @var string
     */
    protected $_referrer;

    /**
     * The user agent the request made with.
     *
     * @var string
     */
    protected $_user_agent;

    /**
     * The IP address of the client.
     *
     * @var string
     */
    protected $_client_ip;

    /**
     * The request body.
     *
     * @var string
     */
    protected $_body;

    /**
     * The request headers.
     *
     * @var array
     */
    protected $_headers = array();

    /**
     *
     * @var array the request cookies
     */
    protected $_cookies = array();

    /**
     * Contains the parameters coming from the matching \c Route instance.
     *
     * Only matters for internal requests.
     *
     * @var array
     */
    protected $_params;

    /**
     * @var string
     */
    protected $_directory;

    /**
     * @var string
     */
    protected $_controller;

    /**
     * @var string
     */
    protected $_action;

    /**
     * @var string
     */
    protected $_lambda_controller;

    /**
     * @var Response
     */
    protected $_response;

    /**
     *
     * @param string $uri the URI of the request represented by this instance.
     */
    public function  __construct($uri = NULL) {
        $this->_uri = $uri;
    }

    public function execute($dispatcher_strategy = NULL) {
        AbstractDispatcher::for_request($this)->dispatch($dispatcher_strategy);
        //return $this->response;
    }

    /**
     * If \c $name is one of the readonly properties then it returns the property.
     * Otherwise it throws an exception.
     *
     * @param string $name
     * @return mixed
     * @throws Exception
     */
    public function __get($name) {
        static $enabled_attributes = array('uri'
            , 'query'
            , 'post'
            , 'method'
            , 'is_ajax'
            , 'params'
            , 'body'
            , 'headers'
            , 'cookies'
            , 'protocol'
            , 'referrer'
            , 'user_agent'
        );
        if (\in_array($name, $enabled_attributes))
            return $this->{'_' . $name};

        throw new \Exception('attribute ' . $name . ' of class '
                . get_class($this) . ' does not exist or is not readable');
    }

    /**
     * Creates a subrequest of the request. The \c $uri property of the subrequest
     * will be the \c $uri parameter. The \c $params, \c $directory, \c $action
     * properties will be set to NULL, every other properties will be inherited
     * from the parent request.
     *
     * @param string $uri
     * @return Request
     */
    public function subrequest($uri) {
        $rval = clone $this;
        $rval->_uri = $uri;
        $rval->_params
                = $rval->_directory
                = $rval->_controller
                = $rval->_action
                = $rval->_lambda_controller = NULL;
        return $rval;
    }

    /**
     * Returns the response for this request. The response protocol will be
     * the same as the request protocol.
     *
     * @return Response
     */
    public function get_response() {
        if (NULL == $this->_response) {
            $this->_response = new Response;
            $this->_response->protocol($this->_protocol);
        }
        return $this->_response;
    }

    /**
     * Sets the \c $query property (the query parameters of the request).
     *
     * @param array $query
     * @return Request $this
     */
    public function query($query) {
        $this->_query = $query;
        return $this;
    }

    /**
     * Sets the \c $post property (the POSTDATA of the request).
     *
     * @param array $post
     * @return Request $this
     */
    public function post($post) {
        $this->_post = $post;
        return $this;
    }

    /**
     * Sets the \c $method property (the HTTP method of the request).
     * 
     * @param string $method
     * @return Request $this
     */
    public function method($method) {
        $this->_method = $method;
        return $this;
    }

    /**
     * Sets the \c $protocol property.
     *
     * @param string $protocol
     * @return Request
     */
    public function protocol($protocol) {
        $this->_protocol = $protocol;
        return $this;
    }

    /**
     * Sets the \c $referrer property.
     *
     * @param $referrer string
     * @return Request
     */
    public function referrer($referrer) {
        $this->_referrer = $referrer;
        return $this;
    }

    /**
     * Sets the \c $user_agent property.
     *
     * @param string $user_agent
     * @return Request
     */
    public function user_agent($user_agent) {
        $this->_user_agent = $user_agent;
        return $this;
    }

   /**
    * Sets the HTTP headers of the request.
    *
    * @param array $headers
    * @return Request
    */
    public function headers($headers) {
        if ( ! (\is_array($headers) || $headers instanceof \ArrayAccess))
            throw new Exception('invalid argument for Request::headers()');
        $this->_headers = $headers;
        return $this;
    }

    /**
     * Sets the cookie parameters of the request
     *
     * @param array $cookies
     * @return Request
     */
    public function cookies($cookies) {
        if ( ! (is_array($cookies) || $cookies instanceof \ArrayAccess))
            throw new \Exception('invalid argument for Request::cookies()');
        $this->_cookies = $cookies;
        return $this;
    }

    /**
     * Sets the \c $is_ajax property of the request.
     * 
     * @param boolean $is_ajax
     * @return Request
     */
    public function is_ajax($is_ajax) {
        $this->_is_ajax = $is_ajax;
        return $this;
    }
    
    /**
     * Sets the parameters of an internal request extracted from the URI.
     * 
     * Only for internal usage
     *
     * @param array $params
     * @usedby Route
     * @return Request $this
     */
    public function params(array $params) {
        $this->_params = $params;
        return $this;
    }
    
    /**
     * Sets the lambda controller if found.
     * 
     * Only for internal usage.
     *
     * @param callback $lambda_controller 
     * @return Request $this
     */
    public function lambda_controller($lambda_controller) {
        $this->_lambda_controller = $lambda_controller;
        return $this;
    }

    /**
     * Merges \c $query with the existing query parameters of the request.
     *
     * @param array $query
     * @return Request
     */
    public function add_query($query) {
        $this->_query = $query + $this->_query;
        return $this;
    }

    /**
     * Merges \c $post with the existing post parameters of the request.
     *
     * @param array $post
     * @return Request
     */
    public function add_post($post) {
        $this->_post = $post + $this->_post;
        return $this;
    }

    /**
     * @param array $headers
     * @return Request
     */
    public function add_headers($headers) {
        $this->_headers = $headers + $this->_headers;
        return $this;
    }


}