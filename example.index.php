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

require cy\LIBPATH.'cyclone/classes/cyclone/autoloader/Kohana.php';
cy\autoloader\Kohana::inst()->register();
require cy\LIBPATH.'cyclone/classes/cyclone/autoloader/Namespaced.php';
cy\autoloader\Namespaced::inst()->register();

spl_autoload_register(array('\\cyclone\\FileSystem', 'autoloader_tests'));

cy\FileSystem::bootstrap(array(
    'app' => cy\APPPATH,
    'cyform' => cy\LIBPATH . 'CyForm/',
    'db' => cy\LIBPATH . 'DB/',
    'jork' => cy\LIBPATH . 'JORK/',
    'logger' => cy\LIBPATH . 'Logger/',
    'cydocs' => cy\TOOLPATH . 'CyDocs/',
    'dbdeploy' => cy\TOOLPATH . 'dbdeploy/',
    'cyclone' => cy\LIBPATH . 'cyclone/',
), cy\APPPATH . '.cache/');
cy\FileSystem::get_default()->run_init_scripts();
cy\Env::init_legacy();

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
