<?php

defined('SYSPATH') or die('No direct script access.');

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

Log::$log_level = Kohana::$environment = Kohana::DEVELOPMENT;

require APPPATH . 'env/' . Kohana::$environment . EXT;



Kohana::$config->attach(new Kohana_Config_File);
/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
            'database' => MODPATH . 'database', // Database access
            'pagination' => MODPATH . 'pagination', // Paging of results
            'core' => MODPATH . 'core',
            'record' => MODPATH . 'record',
            'kform' => MODPATH . 'kform',
            'dev' => MODPATH . 'dev',
            'captcha' => MODPATH . 'captcha'
        ));
Session::instance();

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
        
    }
} else {
    $request->execute();
}

echo $request->send_headers()->response;

