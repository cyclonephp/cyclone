<?php


class Controller_Core extends Controller_Template {

    protected $content;

    protected $params = array();

    protected $template_params = array();

    protected $view_class = 'View';

    protected static $js_params = array();

    public static $resources = array(
            'css' => array(),
            'js' => array()
    );

    public function before() {
        parent::before();
        $this->process_auth();
        if (Request::$is_ajax) {
            $this->auto_render = true;
        }
    }

    protected function process_auth() {
        $auth_cfg = Kohana::config('auth');
        $controller = $this->request->controller;
        $action = $this->request->action;
        foreach (array(
                array_key_exists('#', $auth_cfg) ? $auth_cfg['#'] : true,
                Arr::path($auth_cfg, $controller.'.#', true),
                Arr::path($auth_cfg, $controller.'.'.$action, true)
            ) as $rule) {
            $this->process_auth_rule($rule);
        }
    }

    protected function process_auth_rule($rule) {
        if (is_array($rule) && ! $rule[0]) {
            $this->redirect($rule[1]);
        } else if ( ! $rule) {
            $this->redirect('');
        }
    }

    /**
     * creates content view for the template view then renders response
     * 
     * @see system/classes/kohana/controller/Kohana_Controller_Template#after()
     */
    public function after() {
        $this->action_file_path =  str_replace('_', DIRECTORY_SEPARATOR, $this->request->controller)
                                .DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $this->request->action);
        $this->add_default_resources();
        $this->minify_js();
        $head_view = new View('head_resources');
        $head_view->res = self::$resources;
        $head_view->server_params = self::$js_params;
        $this->template_params['head_resources'] = $head_view;
        if (Request::$is_ajax && $this->auto_render == true) {
            $this->request->response = is_array($this->content) ? 
                json_encode($this->content) :
                $this->content;
            $this->auto_render = false;
        }
        if ($this->auto_render == true) {
            if ($this->content == null) {
                $this->template->_content = new $this->view_class(
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
        parent::after();
    }

    protected function add_default_resources() {
        if (file_exists(DOCROOT.'res/js/'.$this->action_file_path.'.js')) {
            $this->add_js($this->action_file_path);
        }
        if (file_exists(DOCROOT.'res/js/'.$this->request->controller.'.js')) {
            $this->add_js($this->request->controller);
        }

        if (file_exists(DOCROOT.'res/css/'.$this->action_file_path.'.css')) {
            $this->add_css($this->action_file_path);
        }
        if (file_exists(DOCROOT.'res/css/'.$this->request->controller.'.css')) {
            $this->add_css($this->request->controller);
        }

        if (file_exists(DOCROOT.'res/css/template.css')) {
            $this->add_css('template');
        }

        if (file_exists(DOCROOT.'res/js/template.js')) {
            $this->add_js('template');
        }
    }

    protected function minify_js() {
        $delete_keys = array();
        $all_js_files = array(); //array containing the js file names
        foreach (self::$resources['js'] as $k => $item) {
            if ($item['minify']) {
                $delete_keys []= $k;
                $all_js_files []= $item['file'];
            }
        }
        if ( ! empty($delete_keys)) {
            foreach ($delete_keys as $key) {
                unset(self::$resources['js'][$key]);
            }
            $all_filenames = '';
            foreach ($all_js_files as $filename) {
                $all_filenames .= $filename;
            }
            $minified_file_rel_path = '/res/js/'.sha1($all_filenames).'.js';
            $minified_file_abs_path = Text::reduce_slashes(DOCROOT.$minified_file_rel_path);
            if ( ! file_exists($minified_file_abs_path)) {
                $all_js_src = '';
                foreach ($all_js_files as $js_file) {
                    $all_js_src .= JSMin::minify(file_get_contents(Text::reduce_slashes(DOCROOT.$js_file)));
                }
                Log::debug('generating javascript file: '.$minified_file_abs_path);
                file_put_contents($minified_file_abs_path, $all_js_src);
            }
            self::$resources['js'] []= array('file' => $minified_file_rel_path);
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

    public static function add_css($str) {
        $str = '/res/css/'.$str.'.css';
        if (array_search($str, self::$resources['css']) === false) {
            self::$resources['css'] []= $str;
        }
    }

    public static function add_js($str, $minify = true) {
        $str = '/res/js/'.$str.'.js';
        if (array_search($str, self::$resources['js']) === false) {
            self::$resources['js'] []= array('file' => $str, 'minify' => $minify);
        }
    }

    public static function add_js_param($key, $value) {
        self::$js_params[$key] = $value;
    }

    public static function add_js_params(array $params) {
        self::$js_params += $params;
    }
}