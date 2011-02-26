<?php


class Controller_Formtest extends Controller_Core {


    public function action_index() {
        $form = new CyForm('examples/complex');
        $this->params['form'] = $form;
        $this->add_js('jquery');
        $this->add_js('jquery.cyform');
    }

    public function action_multi() {
        if ($this->is_post()) {
            print_r($_POST);
        }
    }
}