<?php

namespace cyclone\request;

/**
 * Represents a HTTP response.
 *
 * @internal The file contains several parts from the HTTP_Response class of Kohana 3.1
 *
 * @property-read $body
 * @property-read $status
 * @property-read $headers
 * @property-read $protocol
 * 
 * @author Bence Erős <crystal@cyclonephp.com>
 * @package cyclone
 */
class Response {


    /**
     * HTTP response codes and their messages
     *
     * @var array
     */
    public static $messages = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );

    /**
     * @return Response
     */
    public static function factory() {
        return new Response;
    }

    /**
     * The response HTTP status code
     *
     * @var int
     */
    protected $_status = 200;

    /**
     * Headers returned in the response
     * 
     * @var  array
     */
    protected $_headers;

    /**
     * The response body
     * 
     * @var  string
     */
    protected $_body = '';

    /**
     * Cookies to be returned in the response
     * 
     * @var array
     */
    protected $_cookies;

    /**
     * The response protocol
     *
     * @var  string
     */
    protected $_protocol = 'HTTP/1.1';

    public function  __construct() {
        $this->_headers = new \ArrayObject;
        $this->_cookies = new \ArrayObject;
    }

    /**
     *
     * @param int $status
     * @return Response
     */
    public function status($status) {
        if ( ! isset(self::$messages[$status]))
            throw new Exception('Invalid status code: ' . $status);
        
        $this->_status = $status;
        return $this;
    }

    /**
     *
     * @param array $headers
     * @return Response
     */
    public function headers($headers) {
        if (is_array($headers)) {
            $headers = new ArrayObject($headers);
        }
        $this->_headers = $headers;
        return $this;
    }

    /**
     *
     * @param string $key
     * @param string $value
     * @return Response
     */
    public function header($key, $value) {
        $this->_headers[$key] = $value;
        return $this;
    }

    public function send_headers() {
        header(
            $this->_protocol . ' ' . $this->_status . ' ' . self::$messages[$this->_status]
        );

        foreach ($this->_headers as $key => $val) {
            if (is_array($val)) {
                $val = \implode(', ', $val);
            }
            header($key . ': ' . $val);
        }

        foreach ($this->_cookies as $key => $val_cnt) {
            
        }
    }

    /**
     *
     * @param string $body
     * @return Response
     */
    public function body($body) {
        $this->_body = $body;
        return $this;
    }

    /**
     *
     * @param array $cookies
     * @return Response
     */
    public function cookies($cookies) {
        if (is_array($cookies)) {
            $cookies = new ArrayObject($cookies);
        }
        $this->_cookies = $cookies;
        return $this;
    }

    /**
     *
     * @param string $key
     * @param int $expiration
     * @param string $val
     */
    public function cookie($key, $val, $expiration) {
        $this->_cookies[$key] = array(
            'value' => $val,
            'expiration' => $expiration
        );
    }

    /**
     *
     * @param string $protocol
     * @return Response
     */
    public function protocol($protocol) {
        $this->_protocol = $protocol;
        return $this;
    }

    /**
     * Returns the length of the body for use with
     * content header
     *
     * @return  int
     */
    public function content_length() {
        return strlen($this->_body);
    }

    public function __get($key) {
        static $enabled_attributes = array(
            'body',
            'status',
            'headers',
            'protocol',
            'status'
        );
        if (in_array($key, $enabled_attributes))
            return $this->{'_' . $key};

        throw new Exception('non-existent or unreadable attribute: ' . $key);
    }

}