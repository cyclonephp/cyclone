<?php

namespace cyclone;

require \cyclone\SYSPATH . 'classes/cyclone/FileSystem.php';

interface Autoloader {

    public function register();

    public function autoload($classname);

    public function list_classes($libs = NULL);

    public function list_testcases($libs = NULL);

}