<?php

namespace Phalcon\Http\Cookie;

use Phalcon\Http\Cookie;
use Phalcon\Http\Response\Cookies;

class Factory extends Cookies
{

	public function __construct($useEncryption = true, $signKey = null)
	{
		parent::__construct($useEncryption, $signKey);
		$this->_registered = true;
	}

	public function make($name, $value = null, $expire = 0, $path = null, $secure = false, $domain = null, $httpOnly = true)
	{
		return new Cookie($name, $value, $expire, $path, $secure, $domain, $httpOnly);
	}

	/**
	 * Create a cookie that lasts "forever" (five years).
	 *
	 * @param  string       $name
	 * @param  string       $value
	 * @param  string       $path
	 * @param  string       $domain
	 * @param  bool|null    $secure
	 * @param  bool         $httpOnly
	 * @return Factory
	 */
	public function forever($name = null, $value = null, $path = null, $secure = false, $domain = null, $httpOnly = true)
	{
		$this->set($name, $value, 2628000, $path, $domain, $secure, $httpOnly);
		return $this;
	}

	/**
	 * Expire the given cookie.
	 *
	 * @param  string  $name
	 * @return bool
	 */
	public function forget($name)
	{
		return $this->delete($name);
	}

}