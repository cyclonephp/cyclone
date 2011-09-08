<?php

define('EXT', '.php');

error_reporting(E_ALL | E_STRICT);

define('DOCROOT', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

define('APPPATH', DOCROOT.'app'.DIRECTORY_SEPARATOR);
define('LIBPATH', DOCROOT.'libs'.DIRECTORY_SEPARATOR);
define('SYSPATH', DOCROOT.'system'.DIRECTORY_SEPARATOR);
define('TOOLPATH', DOCROOT.'tools'.DIRECTORY_SEPARATOR);


if (file_exists('install'.EXT))
{
	// Load the installation check
	return include 'install'.EXT;
}

// Load the base, low-level functions
require SYSPATH.'base'.EXT;

date_default_timezone_set('Europe/Budapest');

//-- Environment setup --------------------------------------------------------


//spl_autoload_register(array('FileSystem', 'autoloader_kohana'));

require SYSPATH . 'classes/autoloader/kohana.php';
Autoloader_Kohana::inst()->register();
require SYSPATH . 'classes/autoloader/namespaced.php';
Autoloader_Namespaced::inst()->register();

spl_autoload_register(array('FileSystem', 'autoloader_tests'));

FileSystem::bootstrap(array(
    'application' => APPPATH,
    'db' => LIBPATH . 'db' . DIRECTORY_SEPARATOR,
    'jork' => LIBPATH . 'jork' . DIRECTORY_SEPARATOR,
    'cyform' => LIBPATH . 'cyform' . DIRECTORY_SEPARATOR,
//    'unittest' => TOOLPATH . 'unittest' . DIRECTORY_SEPARATOR,
    'cytpl' => LIBPATH . 'cytpl' . DIRECTORY_SEPARATOR,
    'logger' => LIBPATH . 'logger' . DIRECTORY_SEPARATOR,
    'cydocs' => TOOLPATH . 'cydocs/',
    'system' => SYSPATH,
), SYSPATH . '.cache' . DIRECTORY_SEPARATOR);

Config::setup();

FileSystem::run_init_scripts();

Env::init_legacy();

ini_set('unserialize_callback_func', 'spl_autoload_call');

Session::instance();

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */

Route::set('default', '(<controller>(/<action>(/<id>)))')
        ->defaults(array(
            'controller' => 'index',
            'action' => 'index',
        ));

if ( ! defined('SUPPRESS_REQUEST')) {
    $request = Request::initial();
    if (Kohana::$environment != Env::DEV) {
        try {
            $request->execute();
        } catch (ReflectionException $ex) {
            log_warning('', '404 not found: ' . Request::instance()->uri);
            $request->redirect(URL::base(), 404);
        } catch (Exception_BadRequest $ex) {
            log_warning('', '500 bad request: ' . Request::instance()->uri);
            $request->redirect(URL::base(), 500);
        } catch (Exception $ex) {
            log_error('', '500 internal error: ' . Request::instance()->uri);
            $request->redirect(URL::base(), 500);
        }
    } else {
        $request->execute();
    }


    echo $request->send_headers()->response;
}
