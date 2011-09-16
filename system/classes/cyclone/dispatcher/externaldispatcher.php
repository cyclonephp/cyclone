<?php

namespace cyclone\dispatcher;

use cyclone as cy;

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package cyclone
 */
class ExternalDispatcher extends AbstractDispatcher {

    const STRATEGY_CURL = 'curl';

    const STRATEGY_PECL_HTTP = 'pecl';

    const STRATEGY_STREAM = 'stream';

    public static $default_strategy = self::STRATEGY_CURL;
        
    public function dispatch($strategy = NULL) {
        if (NULL === $strategy) {
            $strategy = self::$default_strategy;
        }

        cy\Request::notify_execution_start($this->request);
        
        try {
            if (self::STRATEGY_CURL == $strategy) {
                return $this->dispatch_curl();
            } elseif (self::STRATEGY_PECL_HTTP == $strategy) {
                return $this->dispatch_http();
            } elseif (self::STRATEGY_PECL_HTTP == $strategy) {
                return $this->dispatch_stream();
            }
        } catch (\Exception $ex) {
            cy\Request::notify_execution_finish();
            throw new Exception('failed to dispatch request '
                    . $this->request->uri, $ex->getCode(), $ex);
        }
        
        cy\Request::notify_execution_finish();
    }

    public function dispatch_curl() {
        $request = $this->request;
        $method = $request->method;

        $curl_options = array(
            \CURLOPT_CUSTOMREQUEST => $method
        );

        switch ($method) {
            case cy\Request::METHOD_POST:
                $options[\CURLOPT_POSTFIELDS] = \http_build_query($request->post, NULL, '&');
                break;
            case cy\Request::METHOD_PUT:
                
                // Create a temporary file to hold the body
                $body = \tmpfile();
                \fwrite($body, $request->body);
                $length = \ftell($body);
                \fseek($body, 0);
                $curl_options[\CURLOPT_INFILE]     = $body;
                $curl_options[\CURLOPT_INFILESIZE] = $length;
                break;
        }
        if ($headers = $request->headers) {
            $http_headers = array();
            foreach ($headers as $key => $value) {
                $http_headers[] = $key . ': ' . $value;
            }

            $curl_options[\CURLOPT_HTTPHEADER] = $http_headers;
        }

        if ($cookies = $request->cookies) {
            $curl_options[\CURLOPT_COOKIE] = \http_build_query($cookies, NULL, '; ');
	}

        $curl_options[\CURLOPT_RETURNTRANSFER] = TRUE;

        try {
            $curl_options += cy\Config::inst()->get('core.dispatcher.curl');
        } catch (cy\config\Exception $ex) {
            // no additional cURL options found
        }

        $curl = \curl_init($request->uri);

        if ( ! \curl_setopt_array($curl, $curl_options)) {
            throw new Exception('Failed to set CURL options
                , check CURL documentation: http://php.net/curl_setopt_array');
	}

        $resp_body = \curl_exec($curl);
        $resp_code = \curl_getinfo($curl, \CURLINFO_HTTP_CODE);

        if (FALSE === $resp_body) {
            $error = \curl_error($curl);
	}

        \curl_close($curl);

        if (isset($error)) 
            throw new Exception("Error fetching remote {$request->url} [ status {$code} ] {$error}");
        
        //TODO waiting for response class
        throw new Exception('can\'t return Response');

        // copied from Kohana 3.1
        // Create response
		$response = $request->create_response();

		$response->status($code)
			->headers(Request_Client_External::$_processed_headers)
			->body($body);

		return $response;
    }

    public function dispatch_http() {
        $http_method_mapping = array(
            cy\Request::GET => \HTTPRequest::METH_GET,
            cy\Request::HEAD => \HTTPRequest::METH_HEAD,
            cy\Request::POST => \HTTPRequest::METH_POST,
            cy\Request::PUT => \HTTPRequest::METH_PUT,
            cy\Request::DELETE => \HTTPRequest::METH_DELETE,
            cy\Request::OPTIONS => \HTTPRequest::METH_OPTIONS,
            cy\Request::TRACE => \HTTPRequest::METH_TRACE,
            cy\Request::CONNECT => \HTTPRequest::METH_CONNECT,
        );

        $http_request = new \HTTPRequest($this->request->uri, $http_method_mapping[$this->request->method]);

        try {
            $http_request->setOptions(cy\Config::inst()->get('core.dispatcher.http'));
        } catch (cy\config\Exception $ex) {
            // no additional PECL HTTP options found
        }

        $http_request->setHeaders($this->request->headers);
        $http_request->setCookies($this->request->cookies);
        $http_request->setBody($this->request->body);

        try {
            $http_request->send();
	} catch (\Exception $ex) {
            throw new Exception('failed to execute HTTP request :'
                        . $this->request->uri, $ex->getCode(), $ex);
        }

        //TODO waiting for response class
        throw new Exception('can\'t return Response');

        // copied from Kohana 3.1
        // Create the response
	$response = $request->create_response();

		// Build the response
	$response->status($http_request->getResponseCode())
		->headers($http_request->getResponseHeader())
		->cookie($http_request->getResponseCookies())
		->body($http_request->getResponseBody());

	return $response;
    }

    public function dispatch_stream() {
        $mode = ($this->request->method === cy\Request::GET) ? 'r' : 'r+';

        if ($cookies = $this->request->cookies) {
            $this->request->headers('cookie', \http_build_query($cookies, NULL, '; '));
	}

        $body = $this->request->body;

        $this->request->headers('content-length', strlen($body));

        $headers = '';
        foreach ($request->headers as $k => $v) {
            $headers .= $k . ': ' . $v . PHP_EOL;
        }

        $options = array(
            $request->protocol => array(
		'method'     => $request->method,
		'header'     => $headers,
		'content'    => $body,
		'user-agent' => 'CyclonePHP'
            )
	);

        // Create the context stream
	$context = stream_context_create($options);
        stream_context_set_option($context, $this->_options);

        $uri = $request->uri;
        if ($query = $request->query) {
            $uri .= '?'.http_build_query($query, NULL, '&');
	}

        $stream = fopen($uri, $mode, FALSE, $context);
        $meta_data = stream_get_meta_data($stream);

        // Get the HTTP response code
	$http_response = array_shift($meta_data['wrapper_data']);

        if (preg_match_all('/(\w+\/\d\.\d) (\d{3})/', $http_response, $matches)
                !== FALSE) {
            $protocol = $matches[1][0];
            $status   = (int) $matches[2][0];
	} else {
            $protocol = NULL;
            $status   = NULL;
	}

        // Process headers
	array_map(array('Request_Client_External', '_parse_headers'), array(), $meta_data['wrapper_data']);

        // Create a response
	$response = $request->create_response();

	$response->status($status)
		->protocol($protocol)
		->headers(Request_Client_External::$_processed_headers)
		->body(stream_get_contents($stream));

	// Close the stream after use
	fclose($stream);
	return $response;
    }
    
}