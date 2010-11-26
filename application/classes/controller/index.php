<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Index extends Controller_Core {

	public function action_index()
	{
		$this->request->response = 'hello, world!';
	}

    public function action_assets() {
       
    }

}
