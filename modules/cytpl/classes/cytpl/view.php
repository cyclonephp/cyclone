<?php


class CyTpl_View {

    public static function factory($tpl_file ,$data) {
        $view = new CyTpl_View($tpl_file);
        $view->_data = $data;
        return $view;
    }

    private $_tpl_path;

    private $_html_file;

    private $_tpl_name;

    private $_data;

    public function  __construct($tpl_file) {
        $this->_tpl_name = $tpl_file;
        $this->_tpl_path = 'templates/'.$tpl_file;
        $this->_tpl_abs_path = FileSystem::find_file('templates'
                . $this->_tpl_name.  '.tpl');
        $this->_html_file = MODPATH.'cytpl/views/' . $tpl_file . '.php';
    }

    private function compile() {
        $tpl = file_get_contents($this->_tpl_abs_path);

        $html = CyTpl_Compiler::for_template($tpl)->compile();
        file_put_contents($this->_html_file, $html);
    }

    public function render() {
        //$tpl_file = Kohana::find_file('templates', $this->_tpl_name , 'tpl');
        //if ( ! file_exists($this->_html_file)
        //        || filemtime($this->_html_file) < filemtime($tpl_file)) {
            $this->compile();
        //}
        return View::factory($this->_tpl_name, $this->_data)->__toString();
    }

    public function  __toString() {
        try {
            return $this->render();
        } catch (Exception $ex) {
            Kohana::exception_handler($ex);
            return '';
        }
    }
    
}