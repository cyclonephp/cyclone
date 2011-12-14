<?php

namespace cyclone;

require \cyclone\SYSPATH . 'classes/cyclone/FileSystem.php';

interface Autoloader {

    public function register();

    public function autoload($classname);

    public function list_classes($namespace, $with_subnamespaces = TRUE);

}