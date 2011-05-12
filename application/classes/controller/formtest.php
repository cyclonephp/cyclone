<?php


class Controller_Formtest extends Controller_Core {


    public function action_index() {
        $form = new CyForm('examples/complex');
        if ($this->is_post() && $form->set_input($_POST)) {
            echo '<pre>';
            print_r($_POST);
            print_r($form->get_data());
            echo '</pre>';
        }
        $this->params['form'] = $form;
        $this->add_js('jquery');
        $this->add_js('jquery.cyform');
    }

    public function action_multi() {
        if ($this->is_post()) {
            print_r($_POST);
        }
    }

    public function action_ajaxsave() {
        $this->content = array(
            'hello' => 'world'
        );
    }
}