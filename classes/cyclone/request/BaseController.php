<?php

namespace cyclone\request;

use cyclone as cy;
use cyclone\view;

/**
 * The mainly used basic controller, in most cases the concrete
 * controller classes are extended from this class. As in the case
 * of @c cyclone\request\SkeletonController, you will have to implement
 * action methods in the concrete controller class.
 *
 * In the action methods it's not necessary to populate the HTTP response
 * object (<code>$this->_response</code>) manually, the <code>BaseController</code>
 * provides an other way to handle the response, which will be helpful if you want
 * to have a layout page / content page system. The <code>BaseController::before()</code>
 * method (which is executed before
 * the action method) will create two View objects:
 * <ul>
 *  <li> @c BaseController::$_layout will be a view created for rendering the
 *      <code>views/layout.php</code> HTML template.</li>
 *  <li> @c BaseController::$_content will be a view created for the current controller and action.
 *      For example the current actoin method is <code>myapp\controller\FooController::action_bar()</code>
 *      then the content HTML template will be <code>views/myapp/controller/foo/bar.php</code></li>
 * </ul>
 *
 * If the request is an ajax request (see @c Request::$is_ajax ) then <code>BaseController::$_layout</code>
 * won't be created, and <code>BaseController::$_content</code> will be an empty array by default,
 * and it can be used to transfer JSON-encoded data.
 *
 * In most cases, for ajax requests the action method will process the request, and
 * put the data to be returned into the <code>$_content</code> array. In the case of
 * non-ajax requests, the action method will set the parameters of the <code>$this->_content</code>
 * view object, and maybe it will pass some parameters to the layout view (<code>$this->_layout</code>)
 * too.
 *
 * The <code>BaseController::after()</code> method will
 * <ul>
 *  <li>in the case of ajax requests, encode <code>$this->_content</code> into JSON
 *      and assign it to <code>$this->_response->body</code>.</li>
 *  <li>in the case of non-ajax requests it will assign the head script created
 *      by @c AssetPool::get_head_view() to the <code>head_script</code> template variable of
 *      <code>$this->_layout</code> (so in the <code>app/layout.php</code> you can access it
 *          as the <code>$head_script</code> variable), and it will assign the
 *          <code>$this->_content</code> view object to the <code>content</code> template
 *          variable of <code>$this->_layout</code>, and finally it will assign
 *          <code>$this->_layout</code> to <code>$this->_response->body</code>. </li>
 * </ul>
 *
 * To change the file path of the layout HTML template you have to change the value
 * of <code>$this->_layout_file</code> in the <code>before()</code> method of the
 * concrete controller subclass, and you have to do it <b>before calling <code>parent::before()</code></b>.
 *
 * In the case of non-ajax requests the <code>after()</code> method will also add some default
 * resource (asset) files to the @c AssetPool if they are present. These are the followings:
 * <ul>
 *  <li>assets/css/<code>&lt;action_file_path&gt;</code>.css</li>
 *  <li>assets/js/<code>&lt;action_file_path&gt;</code>.js</li>
 *  <li>assets/css/<code>&lt;controller_file_path&gt;</code>.css</li>
 *  <li>assets/js/<code>&lt;controller_file_path&gt;</code>.js</li>
 *  <li>assets/css/<code>&lt;layout_file_path&gt;</code>.css</li>
 *  <li>assets/js/<code>&lt;layout_file_path&gt;</code>.js</li>
 * </ul>
 *
 * If the current action method is <code>myapp\controller\FooController::action_bar()</code> then
 * <ul>
 *  <li><code>&lt;action_file_path&gt;</code> will be myapp/controller/foo/bar</li>
 *  <li><code>&lt;controller_file_path&gt;</code> will be myapp/controller/foo</li>
 * </ul>
 * and <code>&lt;layout_file_path&gt;</code> will be <code>$this->_layout_file</code> (see above).
 *
 * If you override the
 * <code>before()</code> or <code>after()</code> methods, then always
 * call the <code>parent::before()</code> and <code>parent::after()</code>
 * methods too.
 *
 * @package cyclone
 * @author Bence Eros <crystal@cyclonephp.org>
 */
class BaseController extends SkeletonController {

    protected $_auto_render = TRUE;

    protected $content;

    protected $_layout_file = 'layout';

    protected $_layout;

    protected $_content;

    protected $_action_file_path;

    protected $_controller_file_path;

    /**
     * If the request is an AJAX request, then it initializes @c $_content as
     * an empty <code>ArrayObject</code>. Otherwise it creates the layout view object in @c $_layout
     * and the content view object in @c $_content.
     */
    public function before() {
        $params = $this->_request->params;

        $controller_file_path = $params['controller'];
        if (isset($params['namespace'])) { // if the namespace exists then prepend it
            $controller_file_path = \str_replace('\\', \DIRECTORY_SEPARATOR, $params['namespace'])
                    . \DIRECTORY_SEPARATOR . $controller_file_path;
        }

        $this->_controller_file_path = $controller_file_path;
        $this->_action_file_path = $controller_file_path . \DIRECTORY_SEPARATOR . $params['action'];
        
        if ($this->_request->is_ajax) {
            $this->_content = new \ArrayObject(array(), \ArrayObject::ARRAY_AS_PROPS);
        } elseif ($this->_auto_render) {
            $this->_layout = view\AbstractView::factory($this->_layout_file);
            $this->_content = view\AbstractView::factory();
            try {
                $this->_content->set_template($this->_action_file_path);
            } catch (view\ViewException $ex) {
                // the template file doesn't exist - failing silently
            }
        }
    }

    
    public function after() {
        if ( ! $this->_auto_render)
            return;

        if ($this->_req->is_ajax) {
            $this->_resp->body(json_encode($this->_content));
        } else {
            $this->add_default_resources();
            $this->_layout->set('head_script', cy\AssetPool::inst()->get_head_view());
            $this->_layout->set('content', $this->_content);
            $this->_resp->body($this->_layout);
        }
    }

    protected function add_default_resources() {
        try {
            $this->add_js($this->_action_file_path);
        } catch (\Exception $ex) {}
        try {
            $this->add_js($this->_controller_file_path);
        } catch (\Exception $ex) {}

        try {
            $this->add_js($this->_layout_file);
        } catch (\Exception $ex) {}

        try {
            $this->add_css($this->action_file_path);
        } catch (\Exception $ex) {}
        try {
            $this->add_css($this->_controller_file_path);
        } catch (\Exception $ex) {}
        try {
            $this->add_css($this->_layout_file);
        } catch (\Exception $ex) {}
    }

    /**
     * Helper method to determine if the HTTP method of the
     * request is POST.
     *
     * @return boolean
     */
    protected function is_post() {
        return $this->_req->method === Request::METHOD_POST;
    }

    /**
     * Helper method to determine if the HTTP method of the
     * request is GET
     *
     * @return boolean
     */
    protected function is_get() {
        return $this->_req->method === Request::METHOD_GET;
    }

    /**
     * Helper method for using <code>$this->_req->redirect()</code>.
     * Sends a HTTP redirection (Location header) to the client
     * and terminates the request execution.
     *
     * @param string $path an absolute or relative URL
     * @param int $code the HTTP status code of the response
     * @uses Request::redirect()
     */
    protected function redirect($path, $code = 302) {
        $this->_req->redirect($path, $code);
    }

    public static function add_css($str, $minify = TRUE) {
        cy\AssetPool::inst()->add_asset($str, 'css', $minify);
    }

    public static function add_js($str, $minify = TRUE) {
        cy\AssetPool::inst()->add_asset($str, 'js', $minify);
    }

    public static function add_js_param($key, $value) {
        cy\AssetPool::inst()->js_params[$key] = $value;
    }

    public static function add_js_params(array $params) {
        cy\AssetPool::inst()->js_params += $params;
    }
}