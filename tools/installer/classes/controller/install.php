<?php

class Controller_Install extends Controller_Core {

	public function action_index() {
		$this->params['tests'] = Service_Installer::check_environment();
	}

}
