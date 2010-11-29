<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Index extends Controller_Core {

	public function action_index()
	{
		
	}

    public function action_assets() {
       $this->add_css('index/nomin', false);
    }

}
