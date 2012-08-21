<?php

use cyclone as cy;

/**
 * Kohana translation/internationalization function. The PHP function
 * [strtr](http://php.net/strtr) is used for replacing parameters.
 *
 *    __('Welcome back, :user', array(':user' => $username));
 *
 * @uses    I18n::get
 * @param   string  text to translate
 * @param   array   values to replace in the translated text
 * @param   string  target language
 * @return  string
 */
function __($string, array $values = NULL, $lang = 'en-us')
{
	if ($lang !== cy\I18n::$lang)
	{
		// The message and target languages are different
		// Get the translation for this message
		$string = cy\I18n::get($string);
	}

	return empty($values) ? $string : strtr($string, $values);
}