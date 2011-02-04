<?php
class FileSystem {

    public static function find_file($rel_filename){
        if(file_exists(APPPATH.$rel_filename)){
            return APPPATH.$rel_filename;
        }

        $dir_handle = opendir(MODPATH);
        if($dir_handle){
            while(FALSE !== ($fname = readdir($dir_handle))){
                if(($fname != '.') && ($fname != '..') ){
                    if(file_exists(MODPATH.$fname.$rel_filename)){
                        closedir($dir_handle);
                        return MODPATH.$fname.$rel_filename;
                    }
                }
            }
            closedir($dir_handle);
        }

        if(file_exists(SYSPATH.$filename)){
            return SYSPATH.$filename;
        }

        // throw exception if couldn't find the file
    }

    /**
     * TODO pajla
     */
    public static function autoloader_kohana($classname){
        $rel_path = APPPATH.'classes'. DirectoryIterator::str_replace('_', '/', $classname).'php';
        include $this->find_file($classname);

    }

    /**
     * TODO pajla
     */
    public static function cameclass($classname){
        
    }
}
?>
