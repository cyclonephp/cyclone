<?php

use cyclone as cy;
use cyclone\request as req;

define('EXT', '.php');

error_reporting(E_ALL | E_STRICT);

define('cyclone\SYSROOT', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

define('cyclone\APPPATH', cy\SYSROOT.'app'.DIRECTORY_SEPARATOR);
define('cyclone\LIBPATH', cy\SYSROOT.'libs'.DIRECTORY_SEPARATOR);
define('cyclone\SYSPATH', cy\SYSROOT.'system'.DIRECTORY_SEPARATOR);
define('cyclone\TOOLPATH', cy\SYSROOT.'tools'.DIRECTORY_SEPARATOR);


if (file_exists('install'.EXT))
{
	// Load the installation check
	return include 'install'.EXT;
}

// Load the base, low-level functions
require cy\SYSPATH.'base'.EXT;

date_default_timezone_set('Europe/Budapest');

//-- Environment setup --------------------------------------------------------


//spl_autoload_register(array('FileSystem', 'autoloader_kohana'));

require cy\SYSPATH . 'classes/cyclone/autoloader/Kohana.php';
cy\Autoloader\Kohana::inst()->register();
require cy\SYSPATH . 'classes/cyclone/autoloader/Namespaced.php';
cy\Autoloader\Namespaced::inst()->register();

spl_autoload_register(array('\cyclone\FileSystem', 'autoloader_tests'));

cy\FileSystem::bootstrap(array(
    'application' => cy\APPPATH,
    'db' => cy\LIBPATH . 'db' . DIRECTORY_SEPARATOR,
    'jork' => cy\LIBPATH . 'jork' . DIRECTORY_SEPARATOR,
    'cyform' => cy\LIBPATH . 'cyform' . DIRECTORY_SEPARATOR,
//    'unittest' => TOOLPATH . 'unittest' . DIRECTORY_SEPARATOR,
    'cytpl' => cy\LIBPATH . 'cytpl' . DIRECTORY_SEPARATOR,
    'logger' => cy\LIBPATH . 'logger' . DIRECTORY_SEPARATOR,
    'cydocs' => cy\TOOLPATH . 'cydocs/',
    'cyclone' => cy\SYSPATH,
), cy\SYSPATH . '.cache' . DIRECTORY_SEPARATOR);

cy\Config::setup();

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
            'controller' => 'index',
            'action' => 'index',
        ));

if ( ! defined('cyclone\SUPPRESS_REQUEST')) {
    $request = req\Request::initial();
    if (cy\Env::$current == cy\Env::PROD) {
        try {
            $request->execute();
        } catch (ReflectionException $ex) {
            log_warning('', '404 not found: ' . Request::instance()->uri);
            $request->redirect(URL::base(), 404);
        } catch (Exception $ex) {
            log_error('', '500 internal error: ' . Request::instance()->uri);
            $request->redirect(URL::base(), 500);
        }
    } else {
        $request->execute();
    }


    echo $request->send_headers()->response;
}
