<?php
class FileSystem {

    public static function find_file($rel_filename){
        if(is_file(APPPATH.$rel_filename)){
            return APPPATH.$rel_filename;
        }
        
        $dir_handle = opendir(MODPATH);
        if($dir_handle){
            while(FALSE !== ($fname = readdir($dir_handle))){
                if(($fname != '.') && ($fname != '..')){
                    if(file_exists(MODPATH.$fname.DIRECTORY_SEPARATOR.$rel_filename)){
                        closedir($dir_handle);
                        return MODPATH.$fname.DIRECTORY_SEPARATOR.$rel_filename;
                    }
                }
            }
            closedir($dir_handle);
        }

        if(is_file(SYSPATH.$rel_filename)){
            return SYSPATH.$rel_filename;
        }
        
        // throw exception if couldn't find the file
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

    public static function camelcase($classname){
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
