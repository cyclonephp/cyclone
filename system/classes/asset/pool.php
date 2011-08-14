<?php


class Asset_Pool {

    public $assets = array(
            'css' => array(),
            'js' => array()
    );

    public $js_params = array();

    private static $instance;

    /**
     * @return Asset_Pool
     */
    public static function inst() {
        if (NULL === self::$instance) {
            self::$instance = new Asset_Pool;
        }
        return self::$instance;
    }

    private function  __construct() {
        //empty private constructor
    }

    /**
     *
     * @param string $type 'css' or 'js'
     */
    protected function minify_assets($type) {
        $new_resources = array();
        $all_files = array(); //array containing the file names to be minified
        $path = 'assets'.DIRECTORY_SEPARATOR.$type;
        $max_latest_mod = 0; //latest file modification date for asset files to be minified
        foreach ($this->assets[$type] as $k => $minifiable) {
            $abs_path = Kohana::find_file($path, $k, $type);
            if ($minifiable) {
                $all_files []= $k;
                if (($tmp = filemtime($abs_path)) > $max_latest_mod) {
                    $max_latest_mod = $tmp;
                }
            } else {
                $rel_path = substr($abs_path, strlen(DOCROOT));
                $new_resources []= $rel_path;
            }
        }
        if ( ! empty($all_files)) {
            $minified_file_rel_path = Config::inst()->get('core.asset_path').$type.DIRECTORY_SEPARATOR.sha1(implode('', $all_files)).'.'.$type;
            $minified_file_abs_path = DOCROOT.$minified_file_rel_path;
            if ( ! file_exists($minified_file_abs_path)
                    || filemtime($minified_file_abs_path) < $max_latest_mod) {
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
                Log::for_class($this)->add_debug('generating asset file: '.$minified_file_abs_path);
                file_put_contents($minified_file_abs_path, $all_src);
            }
            $new_resources []= $minified_file_rel_path;
        }
        $this->assets[$type] = $new_resources;
    }


    /**
     *
     * @param string $str asset file name
     * @param string $type 'css' or 'js'
     * @param boolean $minify
     */
    public function add_asset($str, $type, $minify = TRUE) {
        if ( ! array_key_exists($str, $this->assets[$type])) {
            if (FALSE === Kohana::find_file('assets' . DIRECTORY_SEPARATOR . $type, $str, $type))
                throw new Exception('asset file not found: '.$str);
            $this->assets[$type][$str] = $minify;
        }
    }


    public function get_head_view() {
        $head_view = new View('head_resources');
        $this->transform_assets();
        $head_view->res = $this->assets;
        $head_view->server_params = $this->js_params;
        return $head_view;
    }

    public function transform_assets() {
        $config = Config::inst();
        foreach (array('js', 'css') as $type) {
            if ($config->get('core.minify.'.$type)) {
                $this->minify_assets($type);
            } else {
                $new_assets = array();
                $path = 'assets'.DIRECTORY_SEPARATOR.$type;
                foreach ($this->assets[$type] as $file => $minify) {
                    $abs_path = Kohana::find_file($path, $file, $type);
                    $new_assets []= substr($abs_path, strlen(DOCROOT));
                }
                $this->assets[$type] = $new_assets;
            }
        }
    }

}