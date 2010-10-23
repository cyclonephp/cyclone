<?php

define('EXT', '.php');

error_reporting(E_ALL | E_STRICT);

/**
 * End of standard configuration! Changing any of the code below should only be
 * attempted by those with a working knowledge of Kohana internals.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 */


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

// Load the core Kohana class
require SYSPATH.'classes/kohana/core'.EXT;

require SYSPATH.'classes/kohana'.EXT;

defined('SYSPATH') or die('No direct script access.');

date_default_timezone_set('Europe/Budapest');

//-- Environment setup --------------------------------------------------------

/**
 * Enable the Kohana auto-loader.
 *
 * @see  http://kohanaframework.org/guide/using.autoloading
 * @see  http://php.net/spl_autoload_register
 */
spl_autoload_register(array('Kohana', 'auto_load'));

/**
 * Enable the Kohana auto-loader for unserialization.
 *
 * @see  http://php.net/spl_autoload_call
 * @see  http://php.net/manual/var.configuration.php#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

//-- environment setup -----------------------------------------

Kohana::$environment = Kohana::DEVELOPMENT;

require APPPATH . 'env/' . Kohana::$environment . EXT;

Kohana::$config->attach(new Kohana_Config_File);

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
            'simpledb' => MODPATH.'simpledb',
            'pagination' => MODPATH . 'pagination',
            'core' => MODPATH . 'core',
            'record' => MODPATH . 'record',
            'kform' => MODPATH . 'kform',
            'dev' => TOOLPATH . 'dev',
            'captcha' => MODPATH . 'captcha',
            'unittest' => TOOLPATH.'unittest',
            'userguide' => TOOLPATH.'userguide',
            'config' => MODPATH.'config'
        ));
Session::instance();

Controller_Core::$minify_js = Kohana::$environment != Kohana::DEVELOPMENT;

Log::$log_level = Kohana::$environment;

register_shutdown_function('Log::write');

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */

Route::set('default', '(<controller>(/<action>(/<id>)))')
        ->defaults(array(
            'controller' => 'index',
            'action' => 'index',
        ));


$request = Request::instance();

if ( ! defined('SUPPRESS_REQUEST'))
if (Kohana::$environment != Kohana::DEVELOPMENT) {
    try {
        $request->execute();
    } catch (ReflectionException $ex) {
        Log::warning('404 not found: '.$_SERVER['PATH_INFO']);
        $request->redirect(URL::base(), 404);
    } catch (Exception_BadRequest $ex) {
        Log::warning('500 bad request: '.$_SERVER['PATH_INFO']);
        $request->redirect(URL::base(), 500);
    } catch (Exception $ex) {
        Log::error('500 internal error: '.$_SERVER['PATH_INFO']);
        $request->redirect(URL::base(), 500);
    }
} else {
    $request->execute();
}

echo $request->send_headers()->response;
