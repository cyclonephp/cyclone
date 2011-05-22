<?php
/**
 * Static class that represents runtime environment configuration.
 *
 * @author Bence Eros <crystal@cyclonephp.com>
 */
class Env {

    /**
     * Constant for marking a development environment.
     *
     * @usedby Env::$current
     */
    const DEV = 'dev';

    /**
     * Constant for marking a testing environment.
     *
     * @usedby Env::$current
     */
    const TEST = 'test';

    /**
     * Constant for marking a production environment.
     *
     * @usedby Env::$current
     */
    const PROD = 'prod';

    /**
     * TRUE if CyclonePHP is executed from the CLI. It doesn't always mean that
     * the cyphp script has been called.
     *
     * The value is detected by Env::init()
     *
     * @var boolean
     */
    public static $is_cli;

    /**
     * TRUE if we are on windows. Detected by Env::init() based in the
     * DIRECTORY_SEPARATOR constant.
     *
     * @var boolean
     */
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

    public static $eol;

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

        self::$eol = self::$is_cli ? PHP_EOL : '<br />';

        set_error_handler('Kohana::error_handler');

        set_exception_handler('Kohana::exception_handler');

        $_GET = Kohana::sanitize($_GET);
        $_POST = Kohana::sanitize($_POST);
        $_COOKIE = Kohana::sanitize($_COOKIE);
    }

    /**
     * Calls Env::init(), then populates the corresponding static variables in
     * the Kohana class.
     *
     * @uses Env::init()
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