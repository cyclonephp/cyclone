<?php

namespace cyclone\view;

/**
 * 
 *
 * @package cyclone
 * @author Bence Eros <crystal@cyclonephp.com>
 */
interface View {

   public function set($key, $val);

   public function bind($key, &$val);

   public static function set_global($key, $val);

   public static function bind_global($key, &$val);

   public function set_template($filename, $is_absolute = FALSE);

   public function render();

}
