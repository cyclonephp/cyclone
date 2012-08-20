<?php
namespace app\controller;

use cyclone\request\SkeletonController;

class MainController extends SkeletonController {

    public function action_index() {
        $this->_response->body('Hello World!');
    }

}