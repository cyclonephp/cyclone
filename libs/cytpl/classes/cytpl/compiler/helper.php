<?php

class CyTpl_Compiler_Helper {

    public static function propchain($str) {
        return str_replace('.', '->', $str);
    }
}