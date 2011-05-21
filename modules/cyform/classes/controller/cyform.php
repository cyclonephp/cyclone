<?php


class Controller_CyForm extends Controller {

    public function action_load() {
        if ( ! array_key_exists('form', $_GET))
            throw new Exception();

        $form = new CyForm($_GET['form']);
        $this->request->response = json_encode(array(
            'html' => $form->render(),
            'css' => Asset_Pool::inst()->assets['css'],
            'js' => Asset_Pool::inst()->assets['js']
        ));
    }
    
}