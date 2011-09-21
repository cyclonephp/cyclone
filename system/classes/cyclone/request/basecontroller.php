<?php

namespace cyclone\request;

use cyclone as cy;


class BaseController extends SkeletonController {

    protected $_auto_render = TRUE;

    protected $content;

    protected $_layout_file = 'layout';

    protected $_layout;

    protected $_content;

    protected $_action_file_path;

    protected $_controller_file_path;

    /**
     * If the request is an AJAX request, then it initializes \c $_content as
     * an empty array. Otherwise it creates the layout view object in \c $_layout
     * and the content view object in \c $_content.
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
            $this->_content = array();
        } else {
            $this->_layout = cy\View::factory($this->_layout_file);
            $this->_content = cy\View::factory($this->_action_file_path);
        }
    }

    
    public function after() {
        $this->add_default_resources();
        if ($this->_request->is_ajax && $this->auto_render == true) {
            $this->request->response = is_array($this->content) ?
                json_encode($this->content) :
                $this->content;
            $this->auto_render = false;
        }
        if ($this->auto_render == true) {
            if ($this->_request->is_ajax) {
                
            } else {
            $this->template_params['head_resources'] = Asset_Pool::inst()->get_head_view();
            if (NULL == $this->content) {
                $this->template->_content = new View(
                       $this->action_file_path,
                        $this->params);
            } else if (is_string($this->content)) {
                $this->template->_content = new View(
                        $this->content,
                        $this->params);
            } else if (is_object($this->content)) {
                $this->template->_content = $this->content;
            } else {
                throw new Exception("unsupported content: ".$this->content);
            }
            foreach ($this->template_params as $k => $v)
                $this->template->$k = $v;
            }
        }
        if ($this->auto_render == TRUE) {
            $this->request->response = $this->template;
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
     * helper method
     *
     * @return boolean
     */
    protected function is_post() {
        return Request::$method == 'POST';
    }

    /**
     * helper method
     *
     * @return boolean
     */
    protected function is_get() {
        return Request::$method == 'GET';
    }

    protected function redirect($path, $partial = true) {
        if ($partial) {
            $this->request->redirect(URL::base().$path);
        } else {
            $this->request->redirect($path);
        }
    }

    public static function add_css($str, $minify = TRUE) {
        Asset_Pool::inst()->add_asset($str, 'css', $minify);
    }

    public static function add_js($str, $minify = TRUE) {
        Asset_Pool::inst()->add_asset($str, 'js', $minify);
    }

    public static function add_js_param($key, $value) {
        Asset_Pool::inst()->js_params[$key] = $value;
    }

    public static function add_js_params(array $params) {
        Asset_Pool::inst()->js_params += $params;
    }
}