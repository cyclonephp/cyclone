<?php

namespace app\controller;

use cyclone\request\BaseController;
use cyclone\view\AbstractView;

/**
 * <p>The action methods in this controller class demonstrate how to handle
 * request parameters in controllers and how to use the HTML templates.</p>
 *
 * <p>To get the class work you should set up the default route in the index.php</p>
 *
 * <p>The BaseController serves as a base class for controllers which want to take the advantage
 * of a master page - content page system. It means that you have a layout template which defines
 * some surrounding UI elements which will appear in every output, and a separate content template
 * is created for every action method.</p>
 *
 * <p>In this example the default layout template will be used which can be found at
 *  app/views/layout.php and for every action method the according
 *  app/views/app/controller/main/&lt;action-name&gt;.php template will act as the
 * content template.</p>
 *
 * @package app
 */
class MainController extends BaseController {


    /**
     * This action will be executed for the following URL-s:
     * <ul>
     *  <li>/</li>
     *  <li>/main/</li>
     *  <li>/main/index/</lil>
     * </ul>
     *
     * <p>The method creates a welcome message and passes it to the HTML template.</p>
     *
     * <p>The content template for this action will be the app/views/app/controller/main/index.php
     * file, because:
     * <ul>
     *  <li>the controller class is in the app\controller namespace</li>
     *  <li>the unqualified name of the controller class is <b>Main</b>Controller</li>
     *  <li>the action method is action_<b>index</b></li>
     * </ul>
     */
    public function action_index() {
        // getting the 'who' query string parameter, or using the default 'Guest' if not found
        // we use the BaseController::get_query() helper method here, but
        // isset($this->_request->query['who']) ? $this->_request->query['who'] : 'Guest'
        // would do the same
        $who = $this->get_query('who', 'Guest');

        // creating the message
        $message = 'Welcome ' . $who;

        // passing the message to the content template
        // in the template file $msg will be a global variable
        $this->_content->msg = $message;

        // the templates can also be used as arrays to set their parameters, so this assignment
        // is the same as the above assignment
        $this->_content['msg'] = $message;
    }

    /**
     * This action will be executed for the following URL-s:
     * <ul>
     *  <li>/main/headers/</lil>
     * </ul>
     *
     * <p>This action method demonstrates how to access the request headers and how to write
     * the response headers.</p>
     *
     * <p>The first function call will set a response header <code>X-Powered-By: CyclonePHP</code>.
     * If you point your browser to http://localhost/cyclonephp/main/headers/ and look at the reponse
     * headers you will see it. (If you don't know how to check the response headers in your browser
     * then you may also run <code>curl -i http://localhost/cyclonephp/main/headers/</code> from the
     * command line.)</p>
     */
    public function action_headers() {
        // setting the response header using $this->_response
        // this object represents the HTTP response to be generated
        $this->_response->header('X-Powered-By', 'CyclonePHP');

        $headers = array();
        // the request headers are available using the $this->_request->headers variable
        // note: for creating this array, Request::initial() uses getallheaders() which does not
        // work in every PHP SAPI-s (see http://php.net/manual/en/function.getallheaders.php ).
        foreach ($this->_request->headers as $name => $value) {
            $headers[$name] = $value;
        }

        $this->_content->headers = $headers;
    }


    /**
     * This action will be executed for the following URL-s:
     * <ul>
     *  <li>/main/showrequest/</li>
     * </ul>
     *
     * <p>This action method gives a more detailed example on how to access request data
     * using the <code>$this->_request</code> property (which is a @c cyclone\Request instance).</p>
     *
     * <p>The request properties are gathered and passed to the content template for rendering,
     * which can be found at app/views/app/controller/main/showrequest.php</p>
     */
    public function action_showrequest() {
        // passing the request method to the template
        $this->_content->method = $this->_request->method;
        // passing if the request is an AJAX request
        $this->_content->is_ajax = $this->_request->is_ajax ? 'Yes' : 'No';
        // passing the user agent
        $this->_content->user_agent = $this->_request->user_agent;
        // passing the request protocol
        $this->_content->protocol = $this->_request->protocol;

        $query_params = array();
        // the query string parameters can be accessed using the
        // $this->_request->query assoc. array
        foreach ($this->_request->query as $k => $v) {
            $query_params[$k] = $v;
        }

        $postdata = array();
        // the POSTDATA can be accessed using the
        // $this->_request->post assoc. array
        foreach ($this->_request->post as $k => $v) {
            $postdata[$k] = $v;
        }

        $cookies = array();
        // the cookies sent by the browser can be accessed using the
        // $this->_request->cookies assoc. array
        foreach ($this->_request->cookies as $k => $v) {
            $cookies[$k] = $v;
        }

        // passing the populated arrays to the view
        // please note that we can also pass request data to the template, so
        // $this->_content->query = $this->_request->query would do the same
        $this->_content->query = $query_params;
        $this->_content->post = $postdata;
        $this->_content->cookies = $cookies;

    }

    /**
     * This action will be executed for the following URL-s:
     * <ul>
     *  <li>/main/embedviews/</li>
     * </ul>
     *
     * <p>This example demonstrates how to create views programmatically and how to embed
     * them into each other. It is an improvement of the previous example, @c action_showrequest() .</p>
     *
     * <p>If you look at app/views/app/controller/main/showreuqest.php you can see that it contains
     * tons of duplicate code. The HTML tables has very similar code except their caption and the
     * source array to be listed. To solve this problem you can create a reusable HTML template and
     * we will create multiple view objects which will render the same template file but with
     * different parameters.
     * </p>
     *
     * <p>In app/controller/main/embedviews.php these embedded views will be simply echoed</p>
     */
    public function action_embedviews() {
        $this->_content->method = $this->_request->method;
        $this->_content->is_ajax = $this->_request->is_ajax ? 'Yes' : 'No';
        $this->_content->user_agent = $this->_request->user_agent;
        $this->_content->protocol = $this->_request->protocol;

        // creating three view objects and passing them to the content template
        // Since $this->_content is a view object created with AbstractView::factory()
        // what we do here is just embedding views into each other
        $this->_content->query = AbstractView::factory('app/controller/main/listing', array(
            'caption' => 'Query string parameters',
            'data' => $this->_request->query
        ));
        $this->_content->post = AbstractView::factory('app/controller/main/listing', array(
            'caption' => 'POSTDATA',
            'data' => $this->_request->post
        ));
        $this->_content->cookies = AbstractView::factory('app/controller/main/listing', array(
            'caption' => 'Cookies',
            'data' => $this->_request->cookies
        ));
    }

    /**
     * This action will be executed for the following URL-s:
     * <ul>
     *  <li>/main/helloassets/</li>
     * </ul>
     *
     * <p>Although neither the action method nor the template file does not do anything,
     * the following asset files will be linked into the output <b>if they are present</b>:
     * <ul>
     *  <li>app/assets/css/app/controller/main/helloasset.css</li>
     *  <li>app/assets/js/app/controller/main/helloasset.js</li>
     * </ul>
     *
     * <p>See the API docs of @c \cyclone\request\BaseController for more details about what is
     * going on under the hood.</p>
     *
     * (Note: if you want to avoid such "magic" behavior then you can extend the
     * @c cyclone\request\SkeletonController class in your application controllers
     * and ignore the @c cyclone\request\BaseController class).
     */
    public function action_helloasset() {

    }

}