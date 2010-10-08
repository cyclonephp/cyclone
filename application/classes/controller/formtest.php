<?php


class Controller_Formtest extends Controller_Core {


    public function action_index() {
        $form = new KForm('examples/complex');
        $this->params['form'] = $form;
    }

    public function action_multi() {
        if ($this->is_post()) {
            print_r($_POST);
        }
    }
}