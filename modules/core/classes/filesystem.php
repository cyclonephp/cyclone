<?php

/**
 * @author Lajos Pajger <pajla@cyclonephp.com>
 */
class FileSystem {

    private static $_roots;

    const MODULE_BOOTSTRAP_FILE = 'init.php';

    public static function bootstrap($roots) {
        self::$_roots = $roots;
//        foreach ($roots as $module_name => $root_path) {
//            if (file_exists($fname =
//                    ($root_path . DIRECTORY_SEPARATOR . self::MODULE_BOOTSTRAP_FILE))) {
//                include $fname;
//            }
//        }
    }

    public static function find_file($rel_filename){
        foreach (self::$_roots as $root_path) {
            $candidate = $root_path . $rel_filename;
            if (file_exists($candidate))
                return $candidate;
        }
        return FALSE;
    }

    public static function autoloader_kohana($classname){
       $classname = strtolower($classname);
       $rel_filename = 'classes/'.str_replace('_', DIRECTORY_SEPARATOR, $classname).'.php';
       
       $result = FileSystem::find_file($rel_filename);
       if($result){
        include_once $result;
        return TRUE;
       }
       return FALSE;
    }

    public static function autoloader_camelcase($classname){
        $rel_filename = 'classes/'.str_replace('_', DIRECTORY_SEPARATOR, $classname).'.php';

        $result = FileSystem::find_file($rel_filename);
        if($result){
            include_once $result;
            return TRUE;
        }
        return FALSE;
    }
}
?>
