#!/usr/bin/env php
<?php

function get_class_name($file) {
	$lines = explode(PHP_EOL, file_get_contents($file));
	foreach ($lines as $line) {
		if (preg_match('/^((interface)|((abstract )?class)) (?P<classname>[a-zA-Z_]+)/', $line, $matches)
			&& isset($matches['classname'])) {
			//print_r($matches);
			return $matches['classname'];
		}
	}
	return NULL;
}
$libname = 'db';
$libs = array('libs/db', 'libs/logger', 'libs/cyform', 'libs/jork', 'tools/cydocs', 'system');

foreach ($libs as $libname) {
	$php_files = explode(PHP_EOL, `find $libname/classes -name '*.php'`);

	foreach ($php_files as $file) {
		if ( ! is_file($file)) continue;
			$class_name = get_class_name($file);
			if (is_null($class_name)) {
				$class_name = 'NOT FOUND';
		}
	
		$dirname = substr($file, 0, strrpos($file, '/')+1);
		rename($file, $dirname . $class_name . '.php');
}
}
