<?php

namespace cyclone;
/**
 * Internationalization (i18n) class. Provides language loading and translation
 * methods without dependancies on [gettext](http://php.net/gettext).
 *
 * Typically this class would never be used directly, but used via the __()
 * function, which loads the message and replaces parameters:
 *
 *     // Display a translated message
 *     echo __('Hello, world');
 *
 *     // With parameter replacement
 *     echo __('Hello, :user', array(':user' => $username));
 *
 * [!!] The __() function is declared in `SYSPATH/base.php`.
 *
 * @package    cyclone
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class I18n {

	/**
	 * @var  string   target language: en-us, es-es, zh-cn, etc
	 */
	public static $lang = 'en-us';

	// Cache of loaded languages
	protected static $_cache = array();

	/**
	 * Get and set the target language.
	 *
	 *     // Get the current language
	 *     $lang = I18n::lang();
	 *
	 *     // Change the current language to Spanish
	 *     I18n::lang('es-es');
	 *
	 * @param   string   new language setting
	 * @return  string
	 * @since   3.0.2
	 */
	public static function lang($lang = NULL)
	{
		if ($lang)
		{
			// Normalize the language
			I18n::$lang = strtolower(str_replace(array(' ', '_'), '-', $lang));
		}

		return I18n::$lang;
	}

	/**
	 * Returns translation of a string. If no translation exists, the original
	 * string will be returned. Example: @code
	 *
	 *     $hello = I18n::get('Hello :name !', array(
         *          ':name' => 'World'
         *     )); @endocde
         *
         *
	 *
	 * @param string   text to translate
         * @param array $values key-value pairs to be replaced in the translated text
         * @param string $lang can be used to specify the target language. If it is
         *  null then the method will fall back to @c I18n::$lang
	 * @return string the transtaled text with the parameters replaced
	 */
        public static function get($string, $values = array(), $lang = NULL) {
            if (NULL === $lang) {
                $lang = static::$lang;
            }
            
            if ( ! isset(I18n::$_cache[$lang])) {
                // Load the translation table
                I18n::load($lang);
            }

            $rval = isset(I18n::$_cache[$lang][$string]) ? I18n::$_cache[$lang][$string] : $string;

            $rval = strtr($string, $values);

            // Return the translated string if it exists
            return $rval;
        }

    /**
	 * Returns the translation table for a given language.
	 *
	 *     // Get all defined Spanish messages
	 *     $messages = I18n::load('es-es');
	 *
	 * @param   string   language to load
	 * @return  array
	 */
	public static function load($lang)
	{
		if (isset(I18n::$_cache[$lang]))
		{
			return I18n::$_cache[$lang];
		}

		// New translation table
		$table = array();

		// Split the language: language, region, locale, etc
		$parts = explode('-', $lang);

		do
		{
			// Create a path for this set of parts
			$path = implode(\DIRECTORY_SEPARATOR, $parts);

			/*if ($t += FileSystem::list_files("i18n/$path.php", TRUE))
			{
				$t = array();
				foreach ($files as $file)
				{
					// Merge the language strings into the sub table
					$t = array_merge($t, require $file);
				}

				// Append the sub table, preventing less specific language
				// files from overloading more specific files
				$table += $t;
			}*/
                        $table += FileSystem::list_files("i18n/$path.php", TRUE);
			// Remove the last part
			array_pop($parts);
		}
		while ($parts);

		// Cache the translation table locally
		return I18n::$_cache[$lang] = $table;
	}

} // End I18n
