<?php

class Log {

    const DEBUG = 'DEBUG';

    const INFO = 'INFO';

    const WARNING = 'WARNING';
    
    const ERROR = 'ERROR';

    public static $log_level;

    public static $level_order = array(
        self::DEBUG => 0,
        self::INFO => 1,
        self::WARNING => 2,
        self::ERROR => 3
    );

    private static $_instances = array();

    private static $_log_cfg;

    /**
     *
     * @param mixed $class
     * @return Log_Adapter
     */
    public static function for_class($class) {
        if (NULL === self::$log_level) {
            self::$log_level = Config::inst()->get('logger.log_level');
        }
        if (is_object($class)) {
            $class = get_class($class);
        }
        if ( ! isset(self::$_instances[$class])) {
            if (NULL === self::$_log_cfg) {
                self::$_log_cfg = Config::inst()->get('logger.adapters');
            }
            $classname_len = strlen($class);
            $longest_matching_prefix_len = NULL;
            $longest_matching_prefix = NULL;
            foreach (self::$_log_cfg as $prefix => $adapter) {
                $prefix_len = strlen($prefix);
                $classname_pref = substr($class, 0, $prefix_len);
                if ($classname_pref == $prefix
                        && $prefix_len >= $longest_matching_prefix_len) {
                        $longest_matching_prefix_len = $prefix_len;
                        $longest_matching_prefix = $prefix;
                }
            }
            if (NULL === $longest_matching_prefix)
                throw new Log_Exception("No logger found for '$class'");
            self::$_instances[$class] = self::$_log_cfg[$longest_matching_prefix];
        }
        return self::$_instances[$class];
    }
}