<?php

class Log {

    const DEBUG = 0;

    const INFO = 1;

    const WARNING = 2;

    const ERROR = 3;

    const TIME_FORMAT = 'h:i:s';

    const ROOT_LOG_PATH = 'logs/';

    const ENTRY_FORMAT = 'time [type] | remote_addr | text';

    private static $log_levels = array(
        Env::DEV => 0,
        Env::TEST => 1,
        Env::PROD => 2
    );

    public static $log_level;

    protected static $messages = array();

    public static function debug($entry) {
        if (self::$log_levels[self::$log_level] >= self::DEBUG) {
            self::add_entry('DEBUG', $entry);
        }
    }

    public static function info($entry) {
        if (self::$log_levels[self::$log_level] >= self::INFO) {
            self::add_entry('INFO', $entry);
        }
    }

    public static function warning($entry) {
        if (self::$log_levels[self::$log_level] >= self::WARNING) {
            self::add_entry('WARNING', $entry);
        }
    }

    public static function error($entry) {
        if (self::$log_levels[self::$log_level] >= self::ERROR) {
            self::add_entry('ERROR', $entry);
        }
    }

    public static function add_entry($type, $entry) {
        self::$messages []= array(
            'type' => $type,
            'text' => $entry,
            'remote_addr' => Arr::get($_SERVER, 'REMOTE_ADDR', 'cli'),
            'time' => date(self::TIME_FORMAT)
        );
    }

    public static function write() {
        $directory = APPPATH.self::ROOT_LOG_PATH.date('Y').DIRECTORY_SEPARATOR.date('m');
        if ( !file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $file = $directory.DIRECTORY_SEPARATOR.date('d').EXT;

        if ( !file_exists($file)) {
            file_put_contents($file, Kohana::FILE_SECURITY.' ?>'.PHP_EOL);
            chmod($file, 0777);
        }

        foreach(self::$messages as $msg) {
            file_put_contents($file, strtr(self::ENTRY_FORMAT, $msg).PHP_EOL, FILE_APPEND);
        }
    }

}