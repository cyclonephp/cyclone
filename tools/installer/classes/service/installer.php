<?php

class Service_Installer {

	public static function check_environment() {
		$rval = array();

		$rval['APPPATH properly set'] = is_dir(APPPATH);
		$rval['MODPATH properly set'] = is_dir(MODPATH);
		$rval['TOOLPATH propertly set'] = is_dir(TOOLPATH);
		$rval['Log dir is writable'] = is_writable(APPPATH.'logs');
		$rval['Cache dir is writable'] = is_writable(APPPATH.'cache');
		$rval['Stub Failure'] = false;
		$rval['SPL Enabled'] = function_exists('spl_autoload_register');
		$rval['Reflection enabled'] = class_exists('ReflectionClass');
		return $rval;
	}

}
