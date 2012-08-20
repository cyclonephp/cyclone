<?php

use cyclone as cy;
use cyclone\request as req;

ob_start();

define('EXT', '.php');

error_reporting(E_ALL | E_STRICT);

define('cyclone\SYSROOT', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

define('cyclone\APPPATH', cy\SYSROOT.'app'.DIRECTORY_SEPARATOR);
define('cyclone\LIBPATH', cy\SYSROOT.'lib'.DIRECTORY_SEPARATOR);
define('cyclone\SYSPATH', cy\LIBPATH.'cyclone'.DIRECTORY_SEPARATOR);
define('cyclone\TOOLPATH', cy\SYSROOT.'tools'.DIRECTORY_SEPARATOR);

date_default_timezone_set('Europe/Budapest');

//-- Environment setup --------------------------------------------------------



require cy\LIBPATH.'cyclone/classes/cyclone/autoloader/Kohana.php';
cy\autoloader\Kohana::inst()->register();
require cy\LIBPATH.'cyclone/classes/cyclone/autoloader/Namespaced.php';
cy\autoloader\Namespaced::inst()->register();

spl_autoload_register(array('\\cyclone\\FileSystem', 'autoloader_tests'));

cy\FileSystem::bootstrap(array(
    'app' => cy\APPPATH,
    'cyform' => cy\LIBPATH . 'CyForm' . DIRECTORY_SEPARATOR,
    'db' => cy\LIBPATH . 'DB' . DIRECTORY_SEPARATOR,
    'jork' => cy\LIBPATH . 'JORK' . DIRECTORY_SEPARATOR,
//    'unittest' => TOOLPATH . 'unittest' . DIRECTORY_SEPARATOR,
    'cytpl' => cy\LIBPATH . 'CyTpl' . DIRECTORY_SEPARATOR,
    'logger' => cy\LIBPATH . 'Logger' . DIRECTORY_SEPARATOR,
    'cydocs' => cy\TOOLPATH . 'CyDocs/',
    'dbdeploy' => cy\TOOLPATH . 'dbdeploy/',
    'cyclone' => cy\LIBPATH . 'cyclone' . DIRECTORY_SEPARATOR,
), cy\APPPATH . '.cache' . DIRECTORY_SEPARATOR);


cy\FileSystem::run_init_scripts();

cy\Env::init_legacy();

ini_set('unserialize_callback_func', 'spl_autoload_call');
cy\Session::instance();

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */

req\Route::set('default', '(<controller>(/<action>(/<id>)))')
        ->defaults(array(
            'controller' => 'main',
            'action' => 'index',
            'namespace' => 'app\\controller'
        ));

if ( ! defined('cyclone\SUPPRESS_REQUEST')) {
    $request = req\Request::initial();
    echo $request->execute()
		->get_response()
		->send_headers()
		->body;
}
