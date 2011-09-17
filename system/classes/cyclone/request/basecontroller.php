<?php

namespace cyclone\request;


class BaseController extends SkeletonController {

    protected $_auto_render = TRUE;

    protected $content;

    protected $_layout_file = 'layout';

    protected $_layout;

    protected $_content;

    protected $_action_file_path;

    /**
     * If the request is an AJAX request, then it initializes \c $_content as
     * an empty array. Otherwise it creates the layout view object in \c $_layout
     * and the content view object in \c $_content.
     */
    public function before() {
        $params = $this->_request->params;
        $action_file_path = $params['controller']
                . \DIRECTORY_SEPARATOR
                . $params['action'];
        if (isset($params['namespace'])) { // if the namespace exists then prepend it
            $action_file_path = \str_replace('\\', \DIRECTORY_SEPARATOR, $params['namespace'])
                    . \DIRECTORY_SEPARATOR . $action_file_path;
        }
        $this->_action_file_path = $action_file_path;
        
        if ($this->_request->is_ajax) {
            $this->_content = array();
        } else {
            $this->_layout = new View($this->_layout_file);
            $this->_content = new View($this->_action_file_path);
        }
    }

    /**
     * creates content view for the template view then renders response
     *
     * @see system/classes/kohana/controller/Kohana_Controller_Template#after()
     */
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
        $asset_path = 'assets/';
        $js_path = $asset_path.'js';
        $css_path = $asset_path.'css';
        
        if (Kohana::find_file($js_path, $this->action_file_path, 'js')) {
            $this->add_js($this->action_file_path);
        }
        if (Kohana::find_file($js_path, $this->request->controller, 'js')) {
            $this->add_js($this->request->controller);
        }

        if (Kohana::find_file($js_path, 'template', 'js')) {
            $this->add_js('template');
        }

        if (Kohana::find_file($css_path, $this->action_file_path, 'css')) {
            $this->add_css($this->action_file_path);
        }
        if (Kohana::find_file($css_path, $this->request->controller, 'css')) {
            $this->add_css($this->request->controller);
        }

        if (Kohana::find_file($css_path, 'template', 'css')) {
            $this->add_css('template');
        }

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