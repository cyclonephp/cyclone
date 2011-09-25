<?php

namespace cyclone\session;

use cyclone as cy;
/**
 * Cookie-based session class.
 *
 * @package    Kohana
 * @category   Session
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class CookieSession extends cy\Session {

	protected function _read($id = NULL)
	{
		return cy\Cookie::get($this->_name, NULL);
	}

	protected function _regenerate()
	{
		// Cookie sessions have no id
		return NULL;
	}

	protected function _write()
	{
		return cy\Cookie::set($this->_name, $this->__toString(), $this->_lifetime);
	}

	protected function _destroy()
	{
		return cy\Cookie::delete($this->_name);
	}

} // End Session_Cookie
