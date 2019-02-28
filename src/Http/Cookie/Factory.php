<?php

namespace Phalcon\Http\Cookie;

use Phalcon\Http\Cookie;

class Factory
{
	/**
	 * @param null $name
	 * @param null $value
	 * @param int $expire
	 * @param null $path
	 * @param bool $secure
	 * @param null $domain
	 * @param bool $httpOnly
	 * @return Cookie
	 */
	public function make($name = null, $value = null, $expire = 0, $path = null, $secure = false, $domain = null, $httpOnly = true)
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
	 * @return Cookie
	 */
	public function forever($name = null, $value = null, $path = null, $secure = false, $domain = null, $httpOnly = true)
	{
		return $this->make($name, $value, 2628000, $path, $domain, $secure, $httpOnly);
	}

	/**
	 * Expire the given cookie.
	 *
	 * @param  string  $name
	 * @param  string  $path
	 * @param  string  $domain
	 * @return Cookie
	 */
	public function forget($name, $path = null, $domain = null)
	{
		return $this->make($name, null, -2628000, $path, $domain);
	}
}