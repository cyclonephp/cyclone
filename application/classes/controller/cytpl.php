<?php

class Controller_CyTpl extends Controller_Core {


    public function action_index() {
        $this->auto_render = false;
        $this->request->response = CyTpl_View::factory('hello', array(
            'a' => 'Hello CyTpl',
            'formtag' => array(
                'action' => '/',
                'method' => 'get'
            ),
            'arr' => array(1, 2, 3)
        ));
    }
    

}