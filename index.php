<?php

define('EXT', '.php');

error_reporting(E_ALL | E_STRICT);

define('DOCROOT', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

define('APPPATH', DOCROOT.'application'.DIRECTORY_SEPARATOR);
define('MODPATH', DOCROOT.'modules'.DIRECTORY_SEPARATOR);
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

include MODPATH . 'core/classes/filesystem.php';
spl_autoload_register(array('FileSystem', 'autoloader_kohana'));

spl_autoload_register(array('FileSystem', 'autoloader_tests'));

FileSystem::bootstrap(array(
    'application' => APPPATH,
    'simpledb' => MODPATH . 'simpledb' . DIRECTORY_SEPARATOR,
    'jork' => MODPATH . 'jork' . DIRECTORY_SEPARATOR,
    'core' => MODPATH . 'core' . DIRECTORY_SEPARATOR,
    'cyform' => MODPATH . 'cyform' . DIRECTORY_SEPARATOR,
    'unittest' => TOOLPATH . 'unittest' . DIRECTORY_SEPARATOR,
    'config' => MODPATH . 'config' . DIRECTORY_SEPARATOR,
    'cytpl' => MODPATH . 'cytpl' . DIRECTORY_SEPARATOR,
    'logger' => MODPATH . 'logger' . DIRECTORY_SEPARATOR,
    'system' => SYSPATH
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

if (!defined('SUPPRESS_REQUEST')) {
    $request = Request::instance();
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
} elseif (Kohana::$is_cli && 'by_cyphp' === SUPPRESS_REQUEST) {
    Cyclone_CLI::bootstrap();
}
