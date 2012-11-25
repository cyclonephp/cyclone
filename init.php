<?php

use cyclone\I18n;

/**
 * Kohana translation/internationalization function. The PHP function
 * <a href="http://php.net/strtr">strtr()</a> is used for replacing parameters.
 *
 * Example:   @code __('Welcome back, :user', array(':user' => $username)); @endcode
 *
 * @uses    \cyclone\I18n::get()
 * @param   string  $text to translate
 * @param   array   $values to replace in the translated text
 * @param   string  $target language
 * @return  string
 */
function __($string, array $values = array(), $lang = NULL) {
    return I18n::get($string, $values, $lang);
}
