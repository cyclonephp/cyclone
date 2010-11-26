<?php


class Controller_Core extends Controller_Template {

    public static $minify_js;

    public static $minify_css;

    public static $config;

    protected $content;

    protected $params = array();

    protected $template_params = array();

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
        if ($this->request == Request::instance()) {
            self::$config = Config::inst();
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
        if (self::$config->get('core.minify.js')) {
            $this->minify_assets('js');
        }
        if (self::$config->get('core.minify.css')) {
            $this->minify_assets('css');
        }
        if (Request::$is_ajax && $this->auto_render == true) {
            $this->request->response = is_array($this->content) ?
                json_encode($this->content) :
                $this->content;
            $this->auto_render = false;
        }
        if ($this->auto_render == true) {
            
            $this->template_params['head_resources'] = $this->create_head_view();
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
        parent::after();
    }

    protected function create_head_view() {
        $head_view = new View('head_resources');
        $head_view->res = self::$resources;
        $head_view->server_params = self::$js_params;
        return $head_view;
    }

    protected function add_default_resources() {
        $asset_path = self::$config->get('core.asset_path');
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
     *
     * @param string $type 'css' or 'js'
     */
    protected function minify_assets($type) {
        $new_resources = array();
        $all_files = array(); //array containing the file names to be minified
        $path = self::$config->get('core.asset_path').$type;
        foreach (self::$resources[$type] as $k => $minifiable) {
            if ($minifiable) {
                $all_files []= $k;
            } else {
                $abs_path = Kohana::find_file($path, $k, 'css');
                $rel_path = substr($abs_path, strlen(DOCROOT));
                $new_resources []= $rel_path;
            }
        }
        if ( ! empty($all_files)) {
            $minified_file_rel_path = 'res'.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.sha1(implode('', $all_files)).'.'.$type;
            $minified_file_abs_path = DOCROOT.$minified_file_rel_path;
            if ( ! file_exists($minified_file_abs_path)) {
                $all_src = '';
                if ($type == 'js') {
                    foreach ($all_files as $file) {
                        $all_src .=
                            JSMin::minify(file_get_contents(Kohana::find_file($path, $file, 'js')));
                    }
                } elseif($type == 'css') {
                    foreach ($all_files as $file) {
                        $all_src .=
                            CssMin::minify(file_get_contents(Kohana::find_file($path, $file, 'css')));
                    }
                }
                Log::debug('generating asset file: '.$minified_file_abs_path);
                file_put_contents($minified_file_abs_path, $all_src);
            }
            $new_resources []= $minified_file_rel_path;
        }
        self::$resources[$type] = $new_resources;
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
        static $path = NULL;
        if (NULL == $path) {
            $path = self::$config->get('core.asset_path').'css';
        }
        if ( ! array_key_exists($str, self::$resources['css'])) {
            if (FALSE === Kohana::find_file($path, $str, 'css'))
                throw new Exception('css file not found: '.$str);
            self::$resources['css'][$str] =  $minify;
        }
    }

    public static function add_js($str, $minify = TRUE) {
        static $path = NULL;
        if (NULL == $path) {
            $path = self::$config->get('core.asset_path').'js';
        }
        if ( ! array_key_exists($str, self::$resources['js'])) {
            if (FALSE === Kohana::find_file($path, $str, 'js'))
                throw new Exception('js file not found: '.$str);            
            self::$resources['js'][$str] = $minify;
        }
    }

    public static function add_js_param($key, $value) {
        self::$js_params[$key] = $value;
    }

    public static function add_js_params(array $params) {
        self::$js_params += $params;
    }
}