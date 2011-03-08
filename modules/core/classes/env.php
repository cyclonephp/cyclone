<?php
/**
 * Static class that represents runtime environment configuration.
 *
 * @author Bence Eros <crystal@cyclonephp.com>
 */
class Env {

    const DEV = 'dev';

    const TEST = 'test';

    const PROD = 'prod';

    public static $is_cli;

    public static $is_windows;

    /**
     * Marks the current enviroment, it's value is recommended to be one of Env::DEV,
     * Env::TEST or Env::PROD
     *
     * @var string
     * @see Env::init()
     */
    public static $current;

    public static $magic_quotes;

    public static $charset = 'utf-8';

    /**
     * Sets up Env::$is_cli, Env::$is_windows and Env::$current. The last one
     * is loaded from the CYCLONEPHP_ENV environment variable, or defaults to
     * Env::DEV if not found.
     *
     * @usedby Env::init_legacy()
     */
    public static function init() {
        self::$is_cli = PHP_SAPI === 'cli';
        self::$is_windows = DIRECTORY_SEPARATOR == '\\';
        self::$current = getenv('CYCLONEPHP_ENV');
        if ( ! self::$current) {
            self::$current = self::DEV;
        }
        self::$magic_quotes = get_magic_quotes_gpc();

        $_GET = Kohana::sanitize($_GET);
        $_POST = Kohana::sanitize($_POST);
        $_COOKIE = Kohana::sanitize($_COOKIE);
    }

    /**
     * Calls Env::init(), then populates the corresponding static variables in
     * the Kohana class.
     */
    public static function init_legacy() {
        self::init();
        Kohana::$environment = self::$current;
        Kohana::$is_cli = self::$is_cli;
        Kohana::$magic_quotes = self::$magic_quotes;
        Kohana::$is_windows = self::$is_windows;
        
        $uri_settings = Config::inst()->get('core.uri');
        Kohana::$index_file = $uri_settings['index_file'];
        Kohana::$base_url = $uri_settings['base_url'];
        
        Kohana::$config = Config::inst();
    }
}