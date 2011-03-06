<?php

class Env {

    const DEV = 'dev';

    const TEST = 'test';

    const PROD = 'prod';

    public static $is_cli;

    public static $is_windows;

    public static $current;

    public static $magic_quotes;

    public static function init() {
        self::$is_cli = PHP_SAPI === 'cli';
        self::$is_windows = DIRECTORY_SEPARATOR == '\\';
        self::$current = getenv('CYCLONEPHP_ENV');
        if ( ! self::$current) {
            self::$current = self::DEV;
        }
        self::$magic_quotes = get_magic_quotes_gpc();
    }

    public static function init_legacy() {
        self::init();
        Kohana::$environment = self::$current;
        Kohana::$is_cli = self::$is_cli;
        Kohana::$magic_quotes = self::$magic_quotes;
        Kohana::$is_windows = self::$is_windows;
        Kohana::$index_file = Config::inst()->get('core.index_file');
        Kohana::$config = Config::inst();
    }
}