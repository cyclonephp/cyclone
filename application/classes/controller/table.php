<?php


class Controller_Table extends Controller_App {

    public function action_index() {
        $this->params['table'] = new Table('sample');
    }
}